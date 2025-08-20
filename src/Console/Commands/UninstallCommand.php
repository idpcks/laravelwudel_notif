<?php

namespace LaravelWudel\LaravelWudelNotif\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use LaravelWudel\LaravelWudelNotif\Services\CacheCleanupService;

class UninstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravelwudel-notif:uninstall {--force : Force uninstall without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall LaravelWudel Notif package and clean up all related files and cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('Are you sure you want to uninstall LaravelWudel Notif package? This will remove all related files and cache.')) {
            $this->info('Uninstall cancelled.');
            return 0;
        }

        $this->info('Starting LaravelWudel Notif package uninstall...');

        try {
            // Clean up cache
            $this->cleanupCache();
            
            // Remove published files
            $this->removePublishedFiles();
            
            // Remove published migrations
            $this->removePublishedMigrations();
            
            // Remove published models
            $this->removePublishedModels();
            
            // Remove published config
            $this->removePublishedConfig();
            
            // Remove published views
            $this->removePublishedViews();
            
            // Remove published assets
            $this->removePublishedAssets();
            
            // Clear all Laravel caches
            $this->clearLaravelCaches();
            
            $this->info('✅ LaravelWudel Notif package has been successfully uninstalled!');
            $this->info('📝 Note: You may need to manually remove any custom code that uses this package.');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error during uninstall: ' . $e->getMessage());
            $this->error('Please check the error and try again, or perform manual cleanup.');
            return 1;
        }
    }

    /**
     * Clean up Laravel cache files
     */
    protected function cleanupCache()
    {
        $this->info('🧹 Cleaning up Laravel cache...');
        
        $cacheService = new CacheCleanupService();
        $results = $cacheService->cleanupPackageCache();
        
        // Display results
        foreach ($results['success'] as $message) {
            $this->line("   ✅ {$message}");
        }
        
        foreach ($results['warnings'] as $message) {
            $this->warn("   ⚠️ {$message}");
        }
        
        foreach ($results['errors'] as $message) {
            $this->error("   ❌ {$message}");
        }
        
        // Validate cache integrity
        $validation = $cacheService->validateCacheIntegrity();
        if (!$validation['valid']) {
            $this->warn('⚠️ Cache validation failed. Performing emergency reset...');
            $emergencyResults = $cacheService->emergencyCacheReset();
            
            foreach ($emergencyResults['success'] as $message) {
                $this->line("   ✅ Emergency: {$message}");
            }
        }
        
        $this->info('✅ Cache cleanup completed.');
    }

    /**
     * Remove published files
     */
    protected function removePublishedFiles()
    {
        $this->info('🗑️ Removing published files...');
        
        $publishedPaths = [
            'app/Models/PushSubscription.php',
            'resources/views/vendor/laravelwudel-notif',
            'public/vendor/laravelwudel-notif'
        ];
        
        foreach ($publishedPaths as $path) {
            if (File::exists($path)) {
                if (File::isDirectory($path)) {
                    File::deleteDirectory($path);
                } else {
                    File::delete($path);
                }
                $this->line("   - Removed: {$path}");
            }
        }
        
        $this->info('✅ Published files removed.');
    }

    /**
     * Remove published migrations
     */
    protected function removePublishedMigrations()
    {
        $this->info('🗑️ Removing published migrations...');
        
        $migrationPath = database_path('migrations');
        $migrations = File::glob($migrationPath . '/*_create_push_subscriptions_table.php');
        
        foreach ($migrations as $migration) {
            if (File::exists($migration)) {
                File::delete($migration);
                $this->line("   - Removed migration: " . basename($migration));
            }
        }
        
        $this->info('✅ Published migrations removed.');
    }

    /**
     * Remove published models
     */
    protected function removePublishedModels()
    {
        $this->info('🗑️ Removing published models...');
        
        $modelPath = app_path('Models/PushSubscription.php');
        if (File::exists($modelPath)) {
            File::delete($modelPath);
            $this->line("   - Removed model: PushSubscription.php");
        }
        
        $this->info('✅ Published models removed.');
    }

    /**
     * Remove published config
     */
    protected function removePublishedConfig()
    {
        $this->info('🗑️ Removing published config...');
        
        $configPath = config_path('laravelwudel-notif.php');
        if (File::exists($configPath)) {
            File::delete($configPath);
            $this->line("   - Removed config: laravelwudel-notif.php");
        }
        
        $this->info('✅ Published config removed.');
    }

    /**
     * Remove published views
     */
    protected function removePublishedViews()
    {
        $this->info('🗑️ Removing published views...');
        
        $viewsPath = resource_path('views/vendor/laravelwudel-notif');
        if (File::exists($viewsPath)) {
            File::deleteDirectory($viewsPath);
            $this->line("   - Removed views directory");
        }
        
        $this->info('✅ Published views removed.');
    }

    /**
     * Remove published assets
     */
    protected function removePublishedAssets()
    {
        $this->info('🗑️ Removing published assets...');
        
        $assetsPath = public_path('vendor/laravelwudel-notif');
        if (File::exists($assetsPath)) {
            File::deleteDirectory($assetsPath);
            $this->line("   - Removed assets directory");
        }
        
        $this->info('✅ Published assets removed.');
    }

    /**
     * Clear all Laravel caches
     */
    protected function clearLaravelCaches()
    {
        $this->info('🧹 Clearing Laravel caches...');
        
        try {
            $cacheService = new CacheCleanupService();
            $results = $cacheService->cleanupPackageCache();
            
            // Display cache clearing results
            foreach ($results['success'] as $message) {
                if (str_contains($message, 'Cleared:')) {
                    $this->line("   - {$message}");
                }
            }
            
        } catch (\Exception $e) {
            $this->warn("   - Warning: Some cache clearing commands failed: " . $e->getMessage());
        }
        
        $this->info('✅ Laravel caches cleared.');
    }

    /**
     * Static method for composer post-package-uninstall hook
     */
    public static function cleanup()
    {
        $command = new self();
        $command->setLaravel(app());
        $command->handle();
    }
}
