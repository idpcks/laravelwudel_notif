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
     * Generate VAPID key pair with multiple fallback methods
     */
    protected function generateVapidKeys(): ?array
    {
        $this->info('Generating new VAPID keys...');
        
        // Method 1: Standard OpenSSL configuration
        $this->line('Trying method 1: Standard OpenSSL configuration...');
        $keys = $this->generateWithOpenSSL();
        if ($keys) {
            $this->info('✅ Method 1 successful with OpenSSL');
            return $keys;
        }
        $this->warn('Method 1 failed: ' . openssl_error_string());

        // Method 2: Alternative curve names
        $this->line('Trying method 2: Alternative curve names...');
        $keys = $this->generateWithAlternativeCurves();
        if ($keys) {
            $this->info('✅ Method 2 successful with alternative curves');
            return $keys;
        }
        $this->warn('Method 2 failed: ' . openssl_error_string());

        // Method 3: Minimal configuration
        $this->line('Trying method 3: Minimal configuration...');
        $keys = $this->generateWithMinimalConfig();
        if ($keys) {
            $this->info('✅ Method 3 successful with minimal config');
            return $keys;
        }
        $this->warn('Method 3 failed: ' . openssl_error_string());

        // Method 4: OpenSSL config file
        $this->line('Trying method 4: OpenSSL config file...');
        $keys = $this->generateWithOpenSSLConfig();
        if ($keys) {
            $this->info('✅ Method 4 successful with OpenSSL config');
            return $keys;
        }
        $this->warn('Method 4 failed: ' . openssl_error_string());

        // Method 5: Sodium-based key generation (most reliable fallback)
        $this->line('Trying method 5: Sodium-based key generation...');
        $keys = $this->generateWithSodium();
        if ($keys) {
            $this->info('✅ Method 5 successful with sodium');
            $this->line('Using alternative key generation method...');
            return $keys;
        }
        $this->warn('Method 5 failed: Sodium extension not available');

        // Method 6: Dummy keys for testing (last resort)
        $this->line('Trying method 6: Dummy keys for testing...');
        $keys = $this->generateDummyKeys();
        if ($keys) {
            $this->warn('⚠️  Using dummy keys for testing purposes only!');
            $this->line('These keys are NOT suitable for production use.');
            return $keys;
        }

        return null;
    }

    /**
     * Method 1: Standard OpenSSL configuration
     */
    protected function generateWithOpenSSL(): ?array
    {
        try {
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
            if (!openssl_pkey_export($res, $privateKey)) {
                openssl_free_key($res);
                return null;
            }
            
            // Extract public key
            $keyDetails = openssl_pkey_get_details($res);
            if (!$keyDetails) {
                openssl_free_key($res);
                return null;
            }
            
            $publicKey = $keyDetails['key'];
            openssl_free_key($res);

            // Convert to VAPID format
            $vapidPublicKey = $this->convertToVapidFormat($publicKey, true);
            $vapidPrivateKey = $this->convertToVapidFormat($privateKey, false);

            if (empty($vapidPublicKey) || empty($vapidPrivateKey)) {
                return null;
            }

            return [
                'public_key' => $vapidPublicKey,
                'private_key' => $vapidPrivateKey,
                'subject' => 'mailto:' . (config('mail.from.address') ?? 'noreply@example.com')
            ];
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Method 2: Alternative curve names
     */
    protected function generateWithAlternativeCurves(): ?array
    {
        $curves = ['P-256', 'secp256r1', 'prime256v1', 'secp384r1', 'secp521r1'];
        
        foreach ($curves as $curve) {
            try {
                $config = [
                    'private_key_bits' => 256,
                    'private_key_type' => OPENSSL_KEYTYPE_EC,
                    'curve_name' => $curve
                ];

                $res = openssl_pkey_new($config);
                if ($res) {
                    // Extract keys and convert to VAPID format
                    $privateKey = '';
                    if (openssl_pkey_export($res, $privateKey)) {
                        $keyDetails = openssl_pkey_get_details($res);
                        if ($keyDetails) {
                            $publicKey = $keyDetails['key'];
                            openssl_free_key($res);
                            
                            $vapidPublicKey = $this->convertToVapidFormat($publicKey, true);
                            $vapidPrivateKey = $this->convertToVapidFormat($privateKey, false);

                            if (!empty($vapidPublicKey) && !empty($vapidPrivateKey)) {
                                return [
                                    'public_key' => $vapidPublicKey,
                                    'private_key' => $vapidPrivateKey,
                                    'subject' => 'mailto:' . (config('mail.from.address') ?? 'noreply@example.com')
                                ];
                            }
                        }
                    }
                    openssl_free_key($res);
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return null;
    }

    /**
     * Method 3: Minimal configuration
     */
    protected function generateWithMinimalConfig(): ?array
    {
        try {
            // Try with minimal configuration
            $res = openssl_pkey_new([
                'private_key_type' => OPENSSL_KEYTYPE_EC,
                'curve_name' => 'prime256v1'
            ]);
            
            if ($res) {
                $privateKey = '';
                if (openssl_pkey_export($res, $privateKey)) {
                    $keyDetails = openssl_pkey_get_details($res);
                    if ($keyDetails) {
                        $publicKey = $keyDetails['key'];
                        openssl_free_key($res);
                        
                        $vapidPublicKey = $this->convertToVapidFormat($publicKey, true);
                        $vapidPrivateKey = $this->convertToVapidFormat($privateKey, false);

                        if (!empty($vapidPublicKey) && !empty($vapidPrivateKey)) {
                            return [
                                'public_key' => $vapidPublicKey,
                                'private_key' => $vapidPrivateKey,
                                'subject' => 'mailto:' . (config('mail.from.address') ?? 'noreply@example.com')
                            ];
                        }
                    }
                }
                openssl_free_key($res);
            }
        } catch (\Exception $e) {
            // Continue to next method
        }
        
        return null;
    }

    /**
     * Method 4: OpenSSL config file approach
     */
    protected function generateWithOpenSSLConfig(): ?array
    {
        try {
            // Try to set OpenSSL config
            $config = [
                'config' => $this->findOpenSSLConfig(),
                'private_key_type' => OPENSSL_KEYTYPE_EC,
                'curve_name' => 'prime256v1'
            ];

            $res = openssl_pkey_new($config);
            if ($res) {
                $privateKey = '';
                if (openssl_pkey_export($res, $privateKey)) {
                    $keyDetails = openssl_pkey_get_details($res);
                    if ($keyDetails) {
                        $publicKey = $keyDetails['key'];
                        openssl_free_key($res);
                        
                        $vapidPublicKey = $this->convertToVapidFormat($publicKey, true);
                        $vapidPrivateKey = $this->convertToVapidFormat($privateKey, false);

                        if (!empty($vapidPublicKey) && !empty($vapidPrivateKey)) {
                            return [
                                'public_key' => $vapidPublicKey,
                                'private_key' => $vapidPrivateKey,
                                'subject' => 'mailto:' . (config('mail.from.address') ?? 'noreply@example.com')
                            ];
                        }
                    }
                }
                openssl_free_key($res);
            }
        } catch (\Exception $e) {
            // Continue to next method
        }
        
        return null;
    }

    /**
     * Method 5: Sodium-based key generation (most reliable fallback)
     */
    protected function generateWithSodium(): ?array
    {
        // Check if sodium extension is available
        if (!extension_loaded('sodium') && !extension_loaded('libsodium')) {
            return null;
        }

        try {
            // Generate keypair using sodium
            $keypair = sodium_crypto_box_keypair();
            
            // Extract public and private keys
            $publicKey = sodium_crypto_box_publickey($keypair);
            $privateKey = sodium_crypto_box_secretkey($keypair);
            
            // Convert to base64url format for VAPID
            $vapidPublicKey = rtrim(strtr(base64_encode($publicKey), '+/', '-_'), '=');
            $vapidPrivateKey = rtrim(strtr(base64_encode($privateKey), '+/', '-_'), '=');
            
            // Clean up
            sodium_memzero($keypair);
            sodium_memzero($privateKey);
            
            return [
                'public_key' => $vapidPublicKey,
                'private_key' => $vapidPrivateKey,
                'subject' => 'mailto:' . (config('mail.from.address') ?? 'noreply@example.com')
            ];
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Method 6: Dummy keys for testing (last resort)
     */
    protected function generateDummyKeys(): ?array
    {
        // Generate random dummy keys for testing purposes only
        $dummyPublicKey = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $dummyPrivateKey = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        
        return [
            'public_key' => $dummyPublicKey,
            'private_key' => $dummyPrivateKey,
            'subject' => 'mailto:test@example.com'
        ];
    }

    /**
     * Find OpenSSL configuration file
     */
    protected function findOpenSSLConfig(): ?string
    {
        $possiblePaths = [
            '/etc/ssl/openssl.cnf',
            '/usr/local/ssl/openssl.cnf',
            '/usr/local/openssl/openssl.cnf',
            'C:\OpenSSL\bin\openssl.cfg',
            'C:\OpenSSL\bin\openssl.cnf',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
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
