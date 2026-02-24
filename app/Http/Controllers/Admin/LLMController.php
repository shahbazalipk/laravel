<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LLMService;
use Illuminate\Http\Request;

class LLMController extends Controller
{
    public function testConnection(Request $request)
    {
        \Log::info('Test connection request received', [
            'provider' => $request->input('provider'),
            'model' => $request->input('model'),
            'has_api_key' => !empty($request->input('api_key'))
        ]);

        try {
            $validated = $request->validate([
                'provider' => 'required|string',
                'model' => 'required|string',
                'api_key' => 'required|string',
            ]);

            $llmService = new LLMService();
            $result = $llmService->testConnection(
                $validated['provider'],
                $validated['model'],
                $validated['api_key']
            );

            \Log::info('Test connection result', $result);

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in test connection', [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . json_encode($e->errors())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Exception in test connection', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generateTemplate(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string',
            'style' => 'nullable|string',
            'sections' => 'nullable|array',
        ]);

        $event = \App\Models\Event::getCurrentEvent();
        
        if (!$event->llm_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'AI features are not enabled for this event'
            ], 400);
        }

        try {
            $llmService = new LLMService($event);
            $html = $llmService->generateLandingPage(
                $validated['prompt'],
                [
                    'style' => $validated['style'] ?? 'modern',
                    'sections' => $validated['sections'] ?? []
                ]
            );

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function improveTemplate(Request $request)
    {
        $validated = $request->validate([
            'html' => 'required|string',
            'css' => 'nullable|string',
            'suggestions' => 'nullable|array',
        ]);

        $event = \App\Models\Event::getCurrentEvent();
        
        if (!$event->llm_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'AI features are not enabled for this event'
            ], 400);
        }

        try {
            $llmService = new LLMService($event);
            $improved = $llmService->improveTemplate(
                $validated['html'],
                $validated['css'] ?? '',
                $validated['suggestions'] ?? []
            );

            return response()->json([
                'success' => true,
                'html' => $improved['html'],
                'css' => $improved['css']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generateSEO(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $event = \App\Models\Event::getCurrentEvent();
        
        if (!$event->llm_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'AI features are not enabled for this event'
            ], 400);
        }

        try {
            $llmService = new LLMService($event);
            $seo = $llmService->generateSEO($validated['content']);

            return response()->json([
                'success' => true,
                'seo' => $seo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
