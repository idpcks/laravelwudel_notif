<?php

namespace LaravelWudel\LaravelWudelNotif\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'push:generate-vapid-keys {--force : Overwrite existing keys}';

    /**
     * The console command description.
     */
    protected $description = 'Generate VAPID keys for web push notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating VAPID keys for web push notifications...');

        // Check if keys already exist
        $envFile = base_path('.env');
        $envContent = File::exists($envFile) ? File::get($envFile) : '';

        if (str_contains($envContent, 'WEBPUSH_VAPID_PUBLIC_KEY') && !$this->option('force')) {
            if (!$this->confirm('VAPID keys already exist. Do you want to regenerate them?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        try {
            // Generate new VAPID keys
            $keys = VAPID::createVapidKeys();

            $this->info('VAPID keys generated successfully!');
            $this->newLine();

            // Display keys
            $this->table(
                ['Key Type', 'Value'],
                [
                    ['Public Key', $keys['publicKey']],
                    ['Private Key', $keys['privateKey']],
                ]
            );

            // Update .env file
            $this->updateEnvFile($keys);

            $this->newLine();
            $this->info('VAPID keys have been added to your .env file.');
            $this->warn('Make sure to keep your private key secure and never commit it to version control!');

            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to generate VAPID keys: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Update the .env file with VAPID keys.
     */
    protected function updateEnvFile(array $keys): void
    {
        $envFile = base_path('.env');
        
        if (!File::exists($envFile)) {
            $this->error('.env file not found. Please create it first.');
            return;
        }

        $envContent = File::get($envFile);
        $lines = explode("\n", $envContent);

        // Prepare new environment variables
        $newVars = [
            'WEBPUSH_VAPID_SUBJECT' => 'mailto:' . (config('app.email') ?? 'admin@example.com'),
            'WEBPUSH_VAPID_PUBLIC_KEY' => $keys['publicKey'],
            'WEBPUSH_VAPID_PRIVATE_KEY' => $keys['privateKey'],
        ];

        foreach ($newVars as $key => $value) {
            $this->updateEnvVariable($lines, $key, $value);
        }

        // Write back to .env file
        File::put($envFile, implode("\n", $lines));
    }

    /**
     * Update or add an environment variable in the .env file.
     */
    protected function updateEnvVariable(array &$lines, string $key, string $value): void
    {
        $found = false;
        
        for ($i = 0; $i < count($lines); $i++) {
            if (str_starts_with(trim($lines[$i]), $key . '=')) {
                $lines[$i] = "{$key}={$value}";
                $found = true;
                break;
            }
        }

        if (!$found) {
            // Add new variable at the end
            $lines[] = "{$key}={$value}";
        }
    }
}
