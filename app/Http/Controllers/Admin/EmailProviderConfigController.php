<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailProviderConfig;
use App\Services\ProviderManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailProviderConfigController extends Controller
{
    public function __construct(
        private ProviderManager $providerManager
    ) {}

    /**
     * Display a listing of provider configurations.
     */
    public function index()
    {
        $configs = EmailProviderConfig::orderBy('is_default', 'desc')
            ->orderBy('provider_name')
            ->get();

        return view('admin.email-provider-configs.index', compact('configs'));
    }

    /**
     * Show the form for creating a new provider configuration.
     */
    public function create()
    {
        $availableProviders = $this->providerManager->getAvailableProviders();
        
        return view('admin.email-provider-configs.create', compact('availableProviders'));
    }

    /**
     * Store a newly created provider configuration.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider_name' => 'required|string|in:infobip,mailchimp,smtp',
            'is_default' => 'boolean',
            'rate_limit' => 'nullable|integer|min:1',
        ]);

        // Validate provider-specific credentials
        $credentials = $this->validateProviderCredentials($request, $validated['provider_name']);

        // Test connection before saving
        try {
            $testResult = $this->providerManager->testProvider($validated['provider_name'], $credentials);
            
            if (!$testResult) {
                return back()
                    ->withInput()
                    ->with('error', 'Connection test failed. Please check your credentials.');
            }
        } catch (\Exception $e) {
            Log::error('Provider connection test failed', [
                'provider' => $validated['provider_name'],
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Connection test failed: ' . $e->getMessage());
        }

        // If setting as default, unset other defaults
        if ($request->boolean('is_default')) {
            EmailProviderConfig::where('is_default', true)->update(['is_default' => false]);
        }

        $config = EmailProviderConfig::create([
            'event_id' => config('event.event_id'),
            'org_id' => config('event.org_id'),
            'provider_name' => $validated['provider_name'],
            'credentials' => $credentials,
            'settings' => [
                'rate_limit' => $validated['rate_limit'] ?? 100,
            ],
            'is_active' => true,
            'is_default' => $request->boolean('is_default'),
            'last_tested_at' => now(),
            'test_status' => 'success',
        ]);

        return redirect()
            ->route('admin.email-campaigns.provider-configs.index')
            ->with('success', 'Provider configuration created successfully.');
    }

    /**
     * Show the form for editing the specified provider configuration.
     */
    public function edit(EmailProviderConfig $providerConfig)
    {
        $availableProviders = $this->providerManager->getAvailableProviders();
        
        return view('admin.email-provider-configs.edit', compact('providerConfig', 'availableProviders'));
    }

    /**
     * Update the specified provider configuration.
     */
    public function update(Request $request, EmailProviderConfig $providerConfig)
    {
        $validated = $request->validate([
            'provider_name' => 'required|string|in:infobip,mailchimp,smtp',
            'is_default' => 'boolean',
            'rate_limit' => 'nullable|integer|min:1',
        ]);

        // Validate provider-specific credentials
        $credentials = $this->validateProviderCredentials($request, $validated['provider_name']);

        // Test connection before saving
        try {
            $testResult = $this->providerManager->testProvider($validated['provider_name'], $credentials);
            
            if (!$testResult) {
                return back()
                    ->withInput()
                    ->with('error', 'Connection test failed. Please check your credentials.');
            }
        } catch (\Exception $e) {
            Log::error('Provider connection test failed', [
                'provider' => $validated['provider_name'],
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Connection test failed: ' . $e->getMessage());
        }

        // If setting as default, unset other defaults
        if ($request->boolean('is_default')) {
            EmailProviderConfig::where('id', '!=', $providerConfig->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $providerConfig->update([
            'provider_name' => $validated['provider_name'],
            'credentials' => $credentials,
            'settings' => [
                'rate_limit' => $validated['rate_limit'] ?? 100,
            ],
            'is_default' => $request->boolean('is_default'),
            'last_tested_at' => now(),
            'test_status' => 'success',
        ]);

        return redirect()
            ->route('admin.email-campaigns.provider-configs.index')
            ->with('success', 'Provider configuration updated successfully.');
    }

    /**
     * Remove the specified provider configuration.
     */
    public function destroy(EmailProviderConfig $providerConfig)
    {
        // Prevent deletion of default provider
        if ($providerConfig->is_default) {
            return back()->with('error', 'Cannot delete the default provider. Set another provider as default first.');
        }

        $providerConfig->delete();

        return redirect()
            ->route('admin.email-campaigns.provider-configs.index')
            ->with('success', 'Provider configuration deleted successfully.');
    }

    /**
     * Test the connection for a provider configuration.
     */
    public function test(EmailProviderConfig $providerConfig)
    {
        try {
            $testResult = $this->providerManager->testProvider(
                $providerConfig->provider_name,
                $providerConfig->credentials
            );

            $providerConfig->update([
                'last_tested_at' => now(),
                'test_status' => $testResult ? 'success' : 'failed',
            ]);

            if ($testResult) {
                return back()->with('success', 'Connection test successful.');
            } else {
                return back()->with('error', 'Connection test failed.');
            }
        } catch (\Exception $e) {
            Log::error('Provider connection test failed', [
                'provider' => $providerConfig->provider_name,
                'error' => $e->getMessage()
            ]);

            $providerConfig->update([
                'last_tested_at' => now(),
                'test_status' => 'failed',
            ]);

            return back()->with('error', 'Connection test failed: ' . $e->getMessage());
        }
    }

    /**
     * Validate provider-specific credentials.
     */
    private function validateProviderCredentials(Request $request, string $providerName): array
    {
        return match ($providerName) {
            'infobip' => $request->validate([
                'api_key' => 'required|string',
                'base_url' => 'required|url',
            ]),
            'mailchimp' => $request->validate([
                'api_key' => 'required|string',
            ]),
            'smtp' => $request->validate([
                'host' => 'required|string',
                'port' => 'required|integer',
                'username' => 'required|string',
                'password' => 'required|string',
                'encryption' => 'required|string|in:tls,ssl,none',
            ]),
            default => throw new \InvalidArgumentException("Unknown provider: {$providerName}"),
        };
    }
}
