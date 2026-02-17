<?php

namespace App\Services;

use App\Contracts\EmailProviderInterface;
use App\Models\EmailProviderConfig;
use Exception;
use Illuminate\Support\Facades\Log;

class ProviderManager
{
    protected array $providers = [];
    protected ?string $defaultProvider = null;
    
    /**
     * Register a provider
     * 
     * @param string $name
     * @param string $class
     * @return void
     */
    public function registerProvider(string $name, string $class): void
    {
        $this->providers[$name] = $class;
    }
    
    /**
     * Get provider instance
     * 
     * @param string|null $name
     * @return EmailProviderInterface
     * @throws Exception
     */
    public function getProvider(?string $name = null): EmailProviderInterface
    {
        // Use default provider if no name specified
        if ($name === null) {
            $config = EmailProviderConfig::active()->default()->first();
            
            if (!$config) {
                // Fallback to SMTP
                return $this->instantiateProvider('smtp', []);
            }
            
            $name = $config->provider_name;
        } else {
            $config = EmailProviderConfig::active()
                ->where('provider_name', $name)
                ->first();
        }
        
        if (!$config) {
            throw new Exception("Provider configuration not found: {$name}");
        }
        
        return $this->instantiateProvider($config->provider_name, $config->credentials);
    }
    
    /**
     * Set default provider
     * 
     * @param string $name
     * @return void
     */
    public function setDefaultProvider(string $name): void
    {
        // Unset current default
        EmailProviderConfig::where('is_default', true)->update(['is_default' => false]);
        
        // Set new default
        EmailProviderConfig::where('provider_name', $name)
            ->update(['is_default' => true]);
        
        $this->defaultProvider = $name;
    }
    
    /**
     * Test provider connection
     * 
     * @param string $name
     * @param array $config
     * @return bool
     */
    public function testProvider(string $name, array $config): bool
    {
        try {
            $provider = $this->instantiateProvider($name, $config);
            return $provider->testConnection();
        } catch (Exception $e) {
            Log::error("Provider test failed: {$name}", ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Get available providers
     * 
     * @return array
     */
    public function getAvailableProviders(): array
    {
        return [
            'infobip' => 'Infobip',
            'mailchimp' => 'Mailchimp Transactional',
            'smtp' => 'SMTP',
        ];
    }
    
    /**
     * Handle provider failover
     * 
     * @param Exception $e
     * @param EmailProviderInterface $provider
     * @return EmailProviderInterface
     */
    public function handleFailover(Exception $e, EmailProviderInterface $provider): EmailProviderInterface
    {
        Log::error('Provider failed, attempting failover', [
            'provider' => get_class($provider),
            'error' => $e->getMessage(),
        ]);
        
        // Fallback to SMTP
        return $this->instantiateProvider('smtp', []);
    }
    
    /**
     * Instantiate a provider
     * 
     * @param string $name
     * @param array $credentials
     * @return EmailProviderInterface
     * @throws Exception
     */
    protected function instantiateProvider(string $name, array $credentials): EmailProviderInterface
    {
        if (!isset($this->providers[$name])) {
            throw new Exception("Provider not registered: {$name}");
        }
        
        $class = $this->providers[$name];
        
        return new $class($credentials);
    }
}
