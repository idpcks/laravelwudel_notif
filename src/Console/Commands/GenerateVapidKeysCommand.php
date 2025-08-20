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

        // Check if keys already exist and are valid
        $envFile = base_path('.env');
        $envContent = File::exists($envFile) ? File::get($envFile) : '';
        
        // Check if keys exist and have actual values (not empty)
        $hasExistingKeys = $this->hasValidExistingKeys($envContent);

        if ($hasExistingKeys && !$this->option('force')) {
            if (!$this->confirm('VAPID keys already exist. Do you want to overwrite them?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        try {
            // Check OpenSSL availability first
            if (!extension_loaded('openssl')) {
                $this->error('❌ OpenSSL extension is not installed or enabled.');
                $this->line('Please install/enable OpenSSL extension in your PHP installation.');
                return 1;
            }

            // Check OpenSSL version and capabilities
            $opensslVersion = OPENSSL_VERSION_TEXT;
            $this->info("OpenSSL Version: {$opensslVersion}");

            // Check if required curves are supported
            if (!$this->checkOpenSSLCurveSupport()) {
                $this->error('❌ OpenSSL does not support required elliptic curves.');
                $this->line('Required curve: prime256v1 (P-256)');
                $this->line('Please update your OpenSSL installation.');
                return 1;
            }

            // Generate new VAPID keys
            $this->info('Generating new VAPID keys...');
            
            $keys = $this->generateVapidKeys();
            
            if (!$keys) {
                $this->error('❌ Failed to generate VAPID keys.');
                $this->line('This might be due to:');
                $this->line('1. OpenSSL configuration issues');
                $this->line('2. Insufficient permissions');
                $this->line('3. PHP OpenSSL extension problems');
                $this->line('');
                $this->line('Please check your OpenSSL installation and try again.');
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
            $this->error('❌ Error generating VAPID keys: ' . $e->getMessage());
            $this->line('');
            $this->line('Debug information:');
            $this->line('- PHP Version: ' . PHP_VERSION);
            $this->line('- OpenSSL Extension: ' . (extension_loaded('openssl') ? 'Loaded' : 'Not loaded'));
            $this->line('- OpenSSL Version: ' . (defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'Unknown'));
            return 1;
        }
    }

    /**
     * Check if valid VAPID keys already exist
     */
    protected function hasValidExistingKeys(string $envContent): bool
    {
        // Check if keys exist and have actual values (not empty or just whitespace)
        $lines = explode("\n", $envContent);
        
        $hasPublicKey = false;
        $hasPrivateKey = false;
        $hasSubject = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (str_starts_with($line, 'WEBPUSH_VAPID_PUBLIC_KEY=')) {
                $value = trim(substr($line, strlen('WEBPUSH_VAPID_PUBLIC_KEY=')));
                $hasPublicKey = !empty($value) && $value !== '""' && $value !== "''";
            }
            
            if (str_starts_with($line, 'WEBPUSH_VAPID_PRIVATE_KEY=')) {
                $value = trim(substr($line, strlen('WEBPUSH_VAPID_PRIVATE_KEY=')));
                $hasPrivateKey = !empty($value) && $value !== '""' && $value !== "''";
            }
            
            if (str_starts_with($line, 'WEBPUSH_VAPID_SUBJECT=')) {
                $value = trim(substr($line, strlen('WEBPUSH_VAPID_SUBJECT=')));
                $hasSubject = !empty($value) && $value !== '""' && $value !== "''";
            }
        }
        
        return $hasPublicKey && $hasPrivateKey && $hasSubject;
    }

    /**
     * Generate VAPID key pair
     */
    protected function generateVapidKeys(): ?array
    {
        try {
            // Generate EC key pair
            $config = [
                'private_key_bits' => 256,
                'private_key_type' => OPENSSL_KEYTYPE_EC,
                'curve_name' => 'prime256v1'
            ];

            $this->line('Creating EC key pair with curve: prime256v1');
            
            $res = openssl_pkey_new($config);
            if (!$res) {
                $this->warn('Failed to create EC key pair. OpenSSL error: ' . openssl_error_string());
                return null;
            }

            $this->line('Extracting private key...');
            
            // Extract private key
            $privateKey = '';
            if (!openssl_pkey_export($res, $privateKey)) {
                $this->warn('Failed to export private key. OpenSSL error: ' . openssl_error_string());
                openssl_free_key($res);
                return null;
            }
            
            $this->line('Extracting public key...');
            
            // Extract public key
            $keyDetails = openssl_pkey_get_details($res);
            if (!$keyDetails) {
                $this->warn('Failed to get key details. OpenSSL error: ' . openssl_error_string());
                openssl_free_key($res);
                return null;
            }
            
            $publicKey = $keyDetails['key'];
            
            openssl_free_key($res);

            $this->line('Converting keys to VAPID format...');
            
            // Convert to VAPID format
            $vapidPublicKey = $this->convertToVapidFormat($publicKey, true);
            $vapidPrivateKey = $this->convertToVapidFormat($privateKey, false);

            if (empty($vapidPublicKey) || empty($vapidPrivateKey)) {
                $this->warn('Failed to convert keys to VAPID format');
                return null;
            }

            return [
                'public_key' => $vapidPublicKey,
                'private_key' => $vapidPrivateKey,
                'subject' => 'mailto:' . (config('mail.from.address') ?? 'noreply@example.com')
            ];
            
        } catch (\Exception $e) {
            $this->warn('Exception during key generation: ' . $e->getMessage());
            return null;
        }
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

    /**
     * Check if required elliptic curves are supported by OpenSSL.
     */
    protected function checkOpenSSLCurveSupport(): bool
    {
        // For OpenSSL 3.0+, we'll use a different approach to check curve support
        // We'll try to generate a key with the required curve to test support
        
        $curves = [
            'prime256v1' => 'P-256',
            'secp384r1' => 'P-384',
            'secp521r1' => 'P-521',
        ];

        foreach ($curves as $curveName => $curveDescription) {
            // Try to create a key pair with the curve to test support
            $config = [
                'private_key_type' => OPENSSL_KEYTYPE_EC,
                'curve_name' => $curveName,
            ];
            
            $res = openssl_pkey_new($config);
            
            if ($res === false) {
                $this->warn("OpenSSL does not support curve: {$curveDescription} ({$curveName})");
                return false;
            }
            
            // Clean up the resource
            openssl_free_key($res);
        }
        
        return true;
    }
}
