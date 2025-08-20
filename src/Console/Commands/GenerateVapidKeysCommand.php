<?php

namespace LaravelWudel\LaravelWudelNotif\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateVapidKeysCommand extends Command
{
    protected $signature = 'push:generate-vapid-keys {--force : Overwrite existing keys}';
    protected $description = 'Generate VAPID keys for web push notifications';

    public function handle()
    {
        $this->info('🚀 LaravelWudel Notif - VAPID Key Generator');
        $this->line('==========================================');

        // Check if keys already exist
        $envFile = base_path('.env');
        $envContent = File::exists($envFile) ? File::get($envFile) : '';
        
        $hasExistingKeys = str_contains($envContent, 'WEBPUSH_VAPID_PUBLIC_KEY') && 
                           str_contains($envContent, 'WEBPUSH_VAPID_PRIVATE_KEY');

        if ($hasExistingKeys && !$this->option('force')) {
            if (!$this->confirm('VAPID keys already exist. Do you want to overwrite them?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        try {
            // Generate new VAPID keys
            $this->info('Generating new VAPID keys...');
            
            $keys = $this->generateVapidKeys();
            
            if (!$keys) {
                $this->error('Failed to generate VAPID keys. Please check your OpenSSL installation.');
                return 1;
            }

            // Update .env file
            $this->updateEnvFile($keys);
            
            // Display keys
            $this->displayKeys($keys);
            
            $this->info('✅ VAPID keys generated and saved successfully!');
            $this->line('');
            $this->line('Next steps:');
            $this->line('1. Restart your application to load new environment variables');
            $this->line('2. Test push notifications in your application');
            
            return 0;

        } catch (\Exception $e) {
            $this->error('Error generating VAPID keys: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate VAPID key pair
     */
    protected function generateVapidKeys(): ?array
    {
        // Generate EC key pair
        $config = [
            'private_key_bits' => 256,
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1'
        ];

        $res = openssl_pkey_new($config);
        if (!$res) {
            return null;
        }

        // Extract private key
        $privateKey = '';
        openssl_pkey_export($res, $privateKey);
        
        // Extract public key
        $keyDetails = openssl_pkey_get_details($res);
        $publicKey = $keyDetails['key'];
        
        openssl_free_key($res);

        // Convert to VAPID format
        $vapidPublicKey = $this->convertToVapidFormat($publicKey, true);
        $vapidPrivateKey = $this->convertToVapidFormat($privateKey, false);

        return [
            'public_key' => $vapidPublicKey,
            'private_key' => $vapidPrivateKey,
            'subject' => 'mailto:' . (config('mail.from.address') ?? 'noreply@example.com')
        ];
    }

    /**
     * Convert PEM key to VAPID format
     */
    protected function convertToVapidFormat(string $pemKey, bool $isPublic): string
    {
        // Remove PEM headers and newlines
        $key = preg_replace('/-----BEGIN.*?-----|-----END.*?-----|\s+/s', '', $pemKey);
        
        // Decode base64
        $decoded = base64_decode($key);
        
        if ($isPublic) {
            // For public key, extract the raw bytes (skip first 26 bytes which contain metadata)
            $rawKey = substr($decoded, 26);
        } else {
            // For private key, extract the raw bytes (skip first 30 bytes which contain metadata)
            $rawKey = substr($decoded, 30);
        }
        
        // Convert to base64url format
        return rtrim(strtr(base64_encode($rawKey), '+/', '-_'), '=');
    }

    /**
     * Update .env file with new keys
     */
    protected function updateEnvFile(array $keys): void
    {
        $envFile = base_path('.env');
        
        if (!File::exists($envFile)) {
            $this->warn('.env file not found. Creating new one...');
            $this->createEnvFile($envFile, $keys);
            return;
        }

        $envContent = File::get($envFile);
        
        // Update or add VAPID keys
        $envContent = $this->updateEnvVariable($envContent, 'WEBPUSH_VAPID_SUBJECT', $keys['subject']);
        $envContent = $this->updateEnvVariable($envContent, 'WEBPUSH_VAPID_PUBLIC_KEY', $keys['public_key']);
        $envContent = $this->updateEnvVariable($envContent, 'WEBPUSH_VAPID_PRIVATE_KEY', $keys['private_key']);

        File::put($envFile, $envContent);
    }

    /**
     * Create new .env file
     */
    protected function createEnvFile(string $envFile, array $keys): void
    {
        $content = "APP_NAME=Laravel\n";
        $content .= "APP_ENV=local\n";
        $content .= "APP_KEY=\n";
        $content .= "APP_DEBUG=true\n";
        $content .= "APP_URL=http://localhost\n\n";
        $content .= "# VAPID Configuration\n";
        $content .= "WEBPUSH_VAPID_SUBJECT={$keys['subject']}\n";
        $content .= "WEBPUSH_VAPID_PUBLIC_KEY={$keys['public_key']}\n";
        $content .= "WEBPUSH_VAPID_PRIVATE_KEY={$keys['private_key']}\n";

        File::put($envFile, $content);
    }

    /**
     * Update environment variable in .env content
     */
    protected function updateEnvVariable(string $content, string $key, string $value): string
    {
        $pattern = "/^{$key}=.*$/m";
        $replacement = "{$key}={$value}";
        
        if (preg_match($pattern, $content)) {
            return preg_replace($pattern, $replacement, $content);
        }
        
        return $content . "\n{$replacement}";
    }

    /**
     * Display generated keys
     */
    protected function displayKeys(array $keys): void
    {
        $this->line('');
        $this->info('Generated VAPID Keys:');
        $this->line('=====================');
        $this->line("Subject: {$keys['subject']}");
        $this->line("Public Key: {$keys['public_key']}");
        $this->line("Private Key: {$keys['private_key']}");
        $this->line('');
        
        $this->warn('⚠️  Keep your private key secure and never share it publicly!');
        $this->line('');
    }
}
