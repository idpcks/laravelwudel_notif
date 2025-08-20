<?php

/**
 * Composer Uninstall Hook for LaravelWudel Notif Package
 * This script is executed automatically when the package is uninstalled
 */

// Prevent direct access
if (!defined('COMPOSER_SCRIPT_EXECUTION')) {
    define('COMPOSER_SCRIPT_EXECUTION', true);
}

// Check if we're in a Laravel environment
function isLaravelEnvironment(): bool
{
    return file_exists('artisan') && 
           file_exists('bootstrap/app.php') && 
           file_exists('vendor/autoload.php');
}

// Emergency cache cleanup function
function emergencyCacheCleanup(): array
{
    $results = ['success' => [], 'warnings' => [], 'errors' => []];
    
    try {
        // Remove bootstrap cache files
        $cacheFiles = [
            'bootstrap/cache/packages.php',
            'bootstrap/cache/services.php',
            'bootstrap/cache/config.php',
            'bootstrap/cache/route.php',
            'bootstrap/cache/view.php'
        ];
        
        foreach ($cacheFiles as $cacheFile) {
            if (file_exists($cacheFile)) {
                $content = file_get_contents($cacheFile);
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
                
                if ($content !== $originalContent) {
                    file_put_contents($cacheFile, $content);
                    $results['success'][] = "Cleaned cache file: {$cacheFile}";
                }
            }
        }
        
        // Remove published files
        $publishedPaths = [
            'app/Models/PushSubscription.php',
            'config/laravelwudel-notif.php',
            'resources/views/vendor/laravelwudel-notif',
            'public/vendor/laravelwudel-notif'
        ];
        
        foreach ($publishedPaths as $path) {
            if (file_exists($path)) {
                if (is_dir($path)) {
                    removeDirectory($path);
                } else {
                    unlink($path);
                }
                $results['success'][] = "Removed: {$path}";
            }
        }
        
        // Remove migrations
        $migrationPath = 'database/migrations';
        if (is_dir($migrationPath)) {
            $migrations = glob($migrationPath . '/*_create_push_subscriptions_table.php');
            foreach ($migrations as $migration) {
                if (file_exists($migration)) {
                    unlink($migration);
                    $results['success'][] = "Removed migration: " . basename($migration);
                }
            }
        }
        
    } catch (Exception $e) {
        $results['errors'][] = 'Cleanup error: ' . $e->getMessage();
    }
    
    return $results;
}

// Helper function to remove directory recursively
function removeDirectory($dir): bool
{
    if (!is_dir($dir)) {
        return false;
    }
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            removeDirectory($path);
        } else {
            unlink($path);
        }
    }
    
    return rmdir($dir);
}

// Main execution
if (isLaravelEnvironment()) {
    echo "🧹 LaravelWudel Notif Package Uninstall Hook\n";
    echo "=============================================\n\n";
    
    try {
        $results = emergencyCacheCleanup();
        
        // Display results
        foreach ($results['success'] as $message) {
            echo "✅ {$message}\n";
        }
        
        foreach ($results['warnings'] as $message) {
            echo "⚠️  {$message}\n";
        }
        
        foreach ($results['errors'] as $message) {
            echo "❌ {$message}\n";
        }
        
        echo "\n✅ Package cleanup completed successfully!\n";
        echo "📝 Your Laravel application should now work without this package.\n";
        
    } catch (Exception $e) {
        echo "❌ Uninstall hook failed: " . $e->getMessage() . "\n";
        echo "⚠️  Please perform manual cleanup if needed.\n";
        exit(1);
    }
} else {
    echo "⚠️  Laravel environment not detected. Skipping cleanup.\n";
}
