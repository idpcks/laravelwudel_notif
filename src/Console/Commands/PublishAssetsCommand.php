<?php

namespace LaravelWudel\LaravelWudelNotif\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'push:publish-assets {--force : Overwrite existing files}';

    /**
     * The console command description.
     */
    protected $description = 'Publish push notification assets to public directory';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Publishing push notification assets...');

        $assets = [
            'js' => [
                'source' => __DIR__ . '/../../resources/js/laravelwudel-notif.js',
                'destination' => public_path('js/laravelwudel-notif.js')
            ],
            'service-worker' => [
                'source' => __DIR__ . '/../../resources/views/service-worker.blade.php',
                'destination' => public_path('sw.js')
            ]
        ];

        $publishedCount = 0;

        foreach ($assets as $type => $asset) {
            if (!$this->publishAsset($asset['source'], $asset['destination'], $type)) {
                return 1;
            }
            $publishedCount++;
        }

        $this->info("Successfully published {$publishedCount} assets.");
        $this->newLine();
        $this->info('Assets published to:');
        $this->line('- JavaScript: /js/laravelwudel-notif.js');
        $this->line('- Service Worker: /sw.js');

        return 0;
    }

    /**
     * Publish a single asset.
     */
    protected function publishAsset(string $source, string $destination, string $type): bool
    {
        if (!File::exists($source)) {
            $this->error("Source file not found: {$source}");
            return false;
        }

        $destinationDir = dirname($destination);
        
        if (!File::exists($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true);
        }

        if (File::exists($destination) && !$this->option('force')) {
            if (!$this->confirm("File {$destination} already exists. Overwrite?")) {
                $this->info("Skipping {$type} asset.");
                return true;
            }
        }

        try {
            File::copy($source, $destination);
            $this->info("Published {$type} asset to: {$destination}");
            return true;
        } catch (\Exception $e) {
            $this->error("Failed to publish {$type} asset: " . $e->getMessage());
            return false;
        }
    }
}
