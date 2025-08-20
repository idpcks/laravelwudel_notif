<?php

namespace LaravelWudel\LaravelWudelNotif\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class CacheCleanupService
{
    /**
     * Clean up all package-related cache
     */
    public function cleanupPackageCache(): array
    {
        $results = [
            'success' => [],
            'warnings' => [],
            'errors' => []
        ];

        try {
            // Clean bootstrap cache files
            $this->cleanBootstrapCache($results);
            
            // Clean Laravel caches
            $this->cleanLaravelCaches($results);
            
            // Clean package-specific caches
            $this->cleanPackageSpecificCaches($results);
            
        } catch (\Exception $e) {
            $results['errors'][] = 'General cleanup error: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Clean bootstrap cache files
     */
    protected function cleanBootstrapCache(array &$results): void
    {
        $cacheFiles = [
            'bootstrap/cache/packages.php',
            'bootstrap/cache/services.php',
            'bootstrap/cache/config.php',
            'bootstrap/cache/route.php',
            'bootstrap/cache/view.php'
        ];

        foreach ($cacheFiles as $cacheFile) {
            if (File::exists($cacheFile)) {
                try {
                    $this->removePackageReferencesFromCache($cacheFile);
                    $results['success'][] = "Cleaned cache file: {$cacheFile}";
                } catch (\Exception $e) {
                    $results['warnings'][] = "Could not clean {$cacheFile}: " . $e->getMessage();
                }
            }
        }
    }

    /**
     * Remove package references from specific cache file
     */
    protected function removePackageReferencesFromCache(string $cachePath): void
    {
        $content = File::get($cachePath);
        $originalContent = $content;

        // Remove package-specific references
        $patterns = [
            '/LaravelWudel\\\\LaravelWudelNotif\\\\LaravelWudelNotifServiceProvider/',
            '/laravelwudel-notif/',
            '/LaravelWudel\\\\LaravelWudelNotif/',
            '/PushNotification/',
            '/WebPushService/'
        ];

        foreach ($patterns as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }

        // Clean up empty arrays and empty lines
        $content = preg_replace('/array\s*\(\s*\)/', 'array()', $content);
        $content = preg_replace('/\n\s*\n/', "\n", $content);

        // Only write if content changed
        if ($content !== $originalContent) {
            File::put($cachePath, $content);
        }
    }

    /**
     * Clean Laravel caches using Artisan commands
     */
    protected function cleanLaravelCaches(array &$results): void
    {
        $commands = [
            'config:clear' => 'Configuration cache',
            'route:clear' => 'Route cache',
            'view:clear' => 'View cache',
            'cache:clear' => 'Application cache',
            'clear-compiled' => 'Compiled classes',
            'optimize:clear' => 'All caches'
        ];

        foreach ($commands as $command => $description) {
            try {
                Artisan::call($command);
                $results['success'][] = "Cleared: {$description}";
            } catch (\Exception $e) {
                $results['warnings'][] = "Could not clear {$description}: " . $e->getMessage();
            }
        }
    }

    /**
     * Clean package-specific caches
     */
    protected function cleanPackageSpecificCaches(array &$results): void
    {
        try {
            // Clear any package-specific cache keys
            $cacheKeys = [
                'laravelwudel-notif',
                'push-notifications',
                'web-push'
            ];

            foreach ($cacheKeys as $key) {
                if (Cache::has($key)) {
                    Cache::forget($key);
                    $results['success'][] = "Cleared cache key: {$key}";
                }
            }
        } catch (\Exception $e) {
            $results['warnings'][] = "Could not clear package-specific caches: " . $e->getMessage();
        }
    }

    /**
     * Validate cache integrity after cleanup
     */
    public function validateCacheIntegrity(): array
    {
        $validation = [
            'valid' => true,
            'issues' => []
        ];

        try {
            // Check if critical cache files exist and are readable
            $criticalFiles = [
                'bootstrap/cache/services.php',
                'bootstrap/cache/packages.php'
            ];

            foreach ($criticalFiles as $file) {
                if (File::exists($file)) {
                    $content = File::get($file);
                    
                    // Check for syntax errors
                    if (!$this->isValidPhpSyntax($content)) {
                        $validation['valid'] = false;
                        $validation['issues'][] = "Syntax error in {$file}";
                    }
                    
                    // Check for empty or corrupted content
                    if (empty(trim($content)) || strlen($content) < 10) {
                        $validation['issues'][] = "Suspicious content in {$file}";
                    }
                }
            }

        } catch (\Exception $e) {
            $validation['valid'] = false;
            $validation['issues'][] = "Validation error: " . $e->getMessage();
        }

        return $validation;
    }

    /**
     * Check if PHP syntax is valid
     */
    protected function isValidPhpSyntax(string $content): bool
    {
        // Basic PHP syntax check
        $content = '<?php ' . $content;
        
        try {
            // Try to tokenize the PHP code
            $tokens = token_get_all($content);
            return !empty($tokens);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Emergency cache reset if validation fails
     */
    public function emergencyCacheReset(): array
    {
        $results = [
            'success' => [],
            'warnings' => [],
            'errors' => []
        ];

        try {
            // Remove all cache files
            $cacheDir = 'bootstrap/cache';
            if (File::exists($cacheDir)) {
                $files = File::glob($cacheDir . '/*.php');
                foreach ($files as $file) {
                    File::delete($file);
                    $results['success'][] = "Removed cache file: " . basename($file);
                }
            }

            // Clear all Laravel caches
            $this->cleanLaravelCaches($results);

        } catch (\Exception $e) {
            $results['errors'][] = "Emergency reset failed: " . $e->getMessage();
        }

        return $results;
    }
}
