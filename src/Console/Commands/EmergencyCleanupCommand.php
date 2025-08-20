<?php

namespace LaravelWudel\LaravelWudelNotif\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use LaravelWudel\LaravelWudelNotif\Services\CacheCleanupService;

class EmergencyCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravelwudel-notif:emergency-cleanup {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Emergency cleanup for LaravelWudel Notif package when cache is corrupted';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('⚠️  EMERGENCY CLEANUP: This will remove ALL cache files and may affect other packages. Continue?')) {
            $this->info('Emergency cleanup cancelled.');
            return 0;
        }

        $this->error('🚨 EMERGENCY CLEANUP MODE ACTIVATED');
        $this->error('This will perform aggressive cache cleanup to resolve critical issues.');
        $this->newLine();

        try {
            // Step 1: Remove all cache files
            $this->removeAllCacheFiles();
            
            // Step 2: Remove all published files
            $this->removeAllPublishedFiles();
            
            // Step 3: Clean composer autoload
            $this->cleanComposerAutoload();
            
            // Step 4: Rebuild everything
            $this->rebuildLaravel();
            
            $this->newLine();
            $this->info('✅ Emergency cleanup completed successfully!');
            $this->info('📝 Your Laravel application should now work normally.');
            $this->newLine();
            $this->warn('⚠️  IMPORTANT: You may need to:');
            $this->warn('   1. Reinstall other packages if needed');
            $this->warn('   2. Reconfigure your application');
            $this->warn('   3. Restart your web server');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Emergency cleanup failed: ' . $e->getMessage());
            $this->error('Please contact support or perform manual cleanup.');
            return 1;
        }
    }

    /**
     * Remove all cache files aggressively
     */
    protected function removeAllCacheFiles()
    {
        $this->info('🗑️  Removing ALL cache files...');
        
        $cacheDirs = [
            'bootstrap/cache',
            'storage/framework/cache',
            'storage/framework/views',
            'storage/framework/sessions'
        ];
        
        foreach ($cacheDirs as $dir) {
            if (File::exists($dir)) {
                $files = File::glob($dir . '/*');
                foreach ($files as $file) {
                    if (File::isFile($file)) {
                        File::delete($file);
                        $this->line("   - Removed: {$file}");
                    } elseif (File::isDirectory($file)) {
                        File::deleteDirectory($file);
                        $this->line("   - Removed directory: {$file}");
                    }
                }
            }
        }
        
        $this->info('✅ All cache files removed.');
    }

    /**
     * Remove all published files
     */
    protected function removeAllPublishedFiles()
    {
        $this->info('🗑️  Removing all published files...');
        
        $publishedPaths = [
            'app/Models/PushSubscription.php',
            'config/laravelwudel-notif.php',
            'resources/views/vendor/laravelwudel-notif',
            'public/vendor/laravelwudel-notif',
            'database/migrations/*_create_push_subscriptions_table.php'
        ];
        
        foreach ($publishedPaths as $path) {
            if (str_contains($path, '*')) {
                // Handle glob patterns
                $files = File::glob($path);
                foreach ($files as $file) {
                    if (File::exists($file)) {
                        if (File::isFile($file)) {
                            File::delete($file);
                            $this->line("   - Removed: {$file}");
                        } else {
                            File::deleteDirectory($file);
                            $this->line("   - Removed directory: {$file}");
                        }
                    }
                }
            } else {
                if (File::exists($path)) {
                    if (File::isFile($path)) {
                        File::delete($path);
                        $this->line("   - Removed: {$path}");
                    } else {
                        File::deleteDirectory($path);
                        $this->line("   - Removed directory: {$path}");
                    }
                }
            }
        }
        
        $this->info('✅ All published files removed.');
    }

    /**
     * Clean composer autoload
     */
    protected function cleanComposerAutoload()
    {
        $this->info('🧹 Cleaning composer autoload...');
        
        $autoloadFiles = [
            'vendor/composer/autoload_classmap.php',
            'vendor/composer/autoload_files.php',
            'vendor/composer/autoload_psr4.php',
            'vendor/composer/autoload_static.php'
        ];
        
        foreach ($autoloadFiles as $file) {
            if (File::exists($file)) {
                File::delete($file);
                $this->line("   - Removed: {$file}");
            }
        }
        
        $this->info('✅ Composer autoload cleaned.');
    }

    /**
     * Rebuild Laravel from scratch
     */
    protected function rebuildLaravel()
    {
        $this->info('🔨 Rebuilding Laravel...');
        
        try {
            // Rebuild composer autoload
            $this->line("   - Rebuilding composer autoload...");
            exec('composer dump-autoload', $output, $returnCode);
            
            if ($returnCode === 0) {
                $this->line("   ✅ Composer autoload rebuilt");
            } else {
                $this->warn("   ⚠️  Composer autoload rebuild failed");
            }
            
            // Clear and rebuild Laravel caches
            $this->line("   - Clearing Laravel caches...");
            $cacheService = new CacheCleanupService();
            $results = $cacheService->emergencyCacheReset();
            
            foreach ($results['success'] as $message) {
                $this->line("   ✅ {$message}");
            }
            
            // Optimize Laravel
            $this->line("   - Optimizing Laravel...");
            exec('php artisan optimize:clear', $output, $returnCode);
            
            if ($returnCode === 0) {
                $this->line("   ✅ Laravel optimized");
            } else {
                $this->warn("   ⚠️  Laravel optimization failed");
            }
            
        } catch (\Exception $e) {
            $this->warn("   ⚠️  Some rebuild steps failed: " . $e->getMessage());
        }
        
        $this->info('✅ Laravel rebuild completed.');
    }
}
