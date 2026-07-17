<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LLMService
{
    protected $event;
    protected $provider;
    protected $model;
    protected $apiKey;
    protected $settings;

    public function __construct(Event $event = null)
    {
        if ($event) {
            $this->event = $event;
            $this->provider = $event->llm_provider;
            $this->model = $event->llm_model;
            $this->apiKey = $event->llm_api_key ? decrypt($event->llm_api_key) : null;
            $this->settings = $event->llm_settings ?? [];
        }
    }

    /**
     * Test connection to LLM provider
     */
    public function testConnection(string $provider, string $model, string $apiKey): array
    {
        Log::info('Testing LLM connection', [
            'provider' => $provider,
            'model' => $model,
            'api_key_length' => strlen($apiKey)
        ]);

        try {
            $response = $this->sendRequest($provider, $model, $apiKey, [
                'prompt' => 'Say "Hello" if you can read this.',
                'max_tokens' => 10
            ]);

            Log::info('LLM test connection successful', [
                'provider' => $provider,
                'model' => $model,
                'response_length' => strlen($response)
            ]);

            return [
                'success' => true,
                'model' => $model,
                'message' => 'Connection successful'
            ];
        } catch (\Exception $e) {
            Log::error('LLM test connection failed', [
                'provider' => $provider,
                'model' => $model,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate landing page HTML from prompt
     */
    public function generateLandingPage(string $prompt, array $options = []): string
    {
        if (!$this->isConfigured()) {
            throw new \Exception('LLM is not configured for this event');
        }

        $systemPrompt = $this->getSystemPrompt();
        $userPrompt = $this->buildLandingPagePrompt($prompt, $options);

        $response = $this->sendRequest($this->provider, $this->model, $this->apiKey, [
            'system' => $systemPrompt,
            'prompt' => $userPrompt,
            'temperature' => $this->settings['temperature'] ?? 0.7,
            'max_tokens' => $this->settings['max_tokens'] ?? 4000
        ]);

        return $this->extractHtmlFromResponse($response);
    }

    /**
     * Improve existing template
     */
    public function improveTemplate(string $html, string $css, array $suggestions = []): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('LLM is not configured for this event');
        }

        $prompt = $this->buildImprovementPrompt($html, $css, $suggestions);

        $response = $this->sendRequest($this->provider, $this->model, $this->apiKey, [
            'prompt' => $prompt,
            'temperature' => 0.5,
            'max_tokens' => 6000
        ]);

        return $this->extractImprovedTemplate($response);
    }

    /**
     * Generate SEO content
     */
    public function generateSEO(string $content): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('LLM is not configured for this event');
        }

        $prompt = "Generate SEO-optimized meta title, description, and keywords for this event:\n\n{$content}\n\nProvide response in JSON format with keys: title, description, keywords";

        $response = $this->sendRequest($this->provider, $this->model, $this->apiKey, [
            'prompt' => $prompt,
            'temperature' => 0.3,
            'max_tokens' => 500
        ]);

        return json_decode($response, true) ?? [];
    }

    /**
     * Send request to LLM provider
     */
    protected function sendRequest(string $provider, string $model, string $apiKey, array $params): string
    {
        switch ($provider) {
            case 'openai':
                return $this->sendOpenAIRequest($model, $apiKey, $params);
            case 'anthropic':
                return $this->sendAnthropicRequest($model, $apiKey, $params);
            case 'google':
                return $this->sendGoogleRequest($model, $apiKey, $params);
            case 'azure':
                return $this->sendAzureRequest($model, $apiKey, $params);
            default:
                throw new \Exception('Unsupported LLM provider');
        }
    }

    /**
     * Send request to OpenAI
     */
    protected function sendOpenAIRequest(string $model, string $apiKey, array $params): string
    {
        $messages = [];
        
        if (isset($params['system'])) {
            $messages[] = ['role' => 'system', 'content' => $params['system']];
        }
        
        $messages[] = ['role' => 'user', 'content' => $params['prompt']];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $params['temperature'] ?? 0.7,
            'max_tokens' => $params['max_tokens'] ?? 4000,
        ]);

        if (!$response->successful()) {
            throw new \Exception('OpenAI API error: ' . $response->body());
        }

        return $response->json()['choices'][0]['message']['content'];
    }

    /**
     * Send request to Anthropic
     */
    protected function sendAnthropicRequest(string $model, string $apiKey, array $params): string
    {
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $model,
            'max_tokens' => $params['max_tokens'] ?? 4000,
            'messages' => [
                ['role' => 'user', 'content' => $params['prompt']]
            ],
            'temperature' => $params['temperature'] ?? 0.7,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Anthropic API error: ' . $response->body());
        }

        return $response->json()['content'][0]['text'];
    }

    /**
     * Send request to Google
     */
    protected function sendGoogleRequest(string $model, string $apiKey, array $params): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}", [
            'contents' => [
                ['parts' => [['text' => $params['prompt']]]]
            ],
            'generationConfig' => [
                'temperature' => $params['temperature'] ?? 0.7,
                'maxOutputTokens' => $params['max_tokens'] ?? 4000,
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('Google API error: ' . $response->body());
        }

        return $response->json()['candidates'][0]['content']['parts'][0]['text'];
    }

    /**
     * Send request to Azure OpenAI
     */
    protected function sendAzureRequest(string $model, string $apiKey, array $params): string
    {
        // Azure requires endpoint configuration
        $endpoint = $this->settings['azure_endpoint'] ?? '';
        
        if (empty($endpoint)) {
            throw new \Exception('Azure endpoint not configured');
        }

        $messages = [];
        
        if (isset($params['system'])) {
            $messages[] = ['role' => 'system', 'content' => $params['system']];
        }
        
        $messages[] = ['role' => 'user', 'content' => $params['prompt']];

        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            'messages' => $messages,
            'temperature' => $params['temperature'] ?? 0.7,
            'max_tokens' => $params['max_tokens'] ?? 4000,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Azure API error: ' . $response->body());
        }

        return $response->json()['choices'][0]['message']['content'];
    }

    /**
     * Check if LLM is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->provider) && !empty($this->model) && !empty($this->apiKey);
    }

    /**
     * Get system prompt for landing page generation
     */
    protected function getSystemPrompt(): string
    {
        return file_get_contents(base_path('TEMPLATE_VARIABLES_REFERENCE.md'));
    }

    /**
     * Build landing page generation prompt
     */
    protected function buildLandingPagePrompt(string $userPrompt, array $options): string
    {
        $style = $options['style'] ?? 'modern';
        $sections = $options['sections'] ?? ['hero', 'about', 'speakers', 'registration', 'sponsors'];
        
        return "Create a landing page template with the following requirements:\n\n" .
               "User Request: {$userPrompt}\n\n" .
               "Style: {$style}\n" .
               "Sections: " . implode(', ', $sections) . "\n\n" .
               "Requirements:\n" .
               "- Use Blade syntax for all dynamic content\n" .
               "- Include complete HTML structure\n" .
               "- Use Tailwind CSS classes or inline styles\n" .
               "- Make it responsive\n" .
               "- Check if collections exist before displaying\n" .
               "- Use storage_public_url(\$path) for images\n\n" .
               "Provide ONLY the HTML code, no explanations.";
    }

    /**
     * Build improvement prompt
     */
    protected function buildImprovementPrompt(string $html, string $css, array $suggestions): string
    {
        $suggestionsText = implode("\n", $suggestions);
        
        return "You are an expert web designer. Improve this landing page template based on the user's request.\n\n" .
               "IMPORTANT RULES:\n" .
               "1. Maintain ALL Blade syntax exactly as is ({{ }}, @if, @foreach, @endforeach, etc.)\n" .
               "2. Keep all variable names unchanged\n" .
               "3. Preserve the overall structure\n" .
               "4. Make the design modern, responsive, and visually appealing\n" .
               "5. Return the response in this exact format:\n" .
               "   HTML content\n" .
               "   <!-- CSS_SEPARATOR -->\n" .
               "   CSS content\n\n" .
               "Current HTML:\n```html\n{$html}\n```\n\n" .
               "Current CSS:\n```css\n{$css}\n```\n\n" .
               "User Request:\n{$suggestionsText}\n\n" .
               "Provide the improved HTML and CSS in the format specified above.";
    }

    /**
     * Extract HTML from response
     */
    protected function extractHtmlFromResponse(string $response): string
    {
        // Remove markdown code blocks if present
        $response = preg_replace('/```html\n?/', '', $response);
        $response = preg_replace('/```\n?/', '', $response);
        
        return trim($response);
    }

    /**
     * Extract improved template from response
     */
    protected function extractImprovedTemplate(string $response): array
    {
        // Try to find CSS_SEPARATOR
        if (strpos($response, '<!-- CSS_SEPARATOR -->') !== false) {
            $parts = explode('<!-- CSS_SEPARATOR -->', $response);
            $html = trim($parts[0]);
            $css = isset($parts[1]) ? trim($parts[1]) : '';
            
            // Clean up markdown code blocks if present
            $html = preg_replace('/```html\n?/', '', $html);
            $html = preg_replace('/```\n?$/', '', $html);
            $css = preg_replace('/```css\n?/', '', $css);
            $css = preg_replace('/```\n?$/', '', $css);
            
            return [
                'html' => trim($html),
                'css' => trim($css)
            ];
        }
        
        // Try JSON format
        $data = json_decode($response, true);
        if ($data && isset($data['html'])) {
            return [
                'html' => $data['html'],
                'css' => $data['css'] ?? ''
            ];
        }
        
        // Try to extract HTML and CSS from markdown code blocks
        preg_match('/```html\n(.*?)```/s', $response, $htmlMatches);
        preg_match('/```css\n(.*?)```/s', $response, $cssMatches);
        
        if (!empty($htmlMatches[1])) {
            return [
                'html' => trim($htmlMatches[1]),
                'css' => !empty($cssMatches[1]) ? trim($cssMatches[1]) : ''
            ];
        }
        
        // Last resort: assume the entire response is HTML
        return [
            'html' => trim($response),
            'css' => ''
        ];
    }
}
