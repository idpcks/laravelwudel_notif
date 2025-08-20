#!/bin/bash

echo "========================================"
echo "LaravelWudel Notif Package Uninstaller"
echo "========================================"
echo

echo "Starting uninstall process..."
echo

# Check if Laravel is available
if ! php artisan --version >/dev/null 2>&1; then
    echo "ERROR: Laravel not found or not accessible"
    echo "Please run this script from your Laravel project root directory"
    exit 1
fi

echo "1. Running Laravel uninstall command..."
if php artisan laravelwudel-notif:uninstall --force; then
    echo "✅ Laravel command completed successfully"
else
    echo
    echo "⚠️  WARNING: Laravel command failed, performing manual cleanup..."
    echo
    
    echo "2. Removing published files..."
    if [ -f "app/Models/PushSubscription.php" ]; then
        rm -f "app/Models/PushSubscription.php"
        echo "   - Removed PushSubscription model"
    fi
    
    if [ -f "config/laravelwudel-notif.php" ]; then
        rm -f "config/laravelwudel-notif.php"
        echo "   - Removed config file"
    fi
    
    if [ -d "resources/views/vendor/laravelwudel-notif" ]; then
        rm -rf "resources/views/vendor/laravelwudel-notif"
        echo "   - Removed views directory"
    fi
    
    if [ -d "public/vendor/laravelwudel-notif" ]; then
        rm -rf "public/vendor/laravelwudel-notif"
        echo "   - Removed assets directory"
    fi
    
    echo "3. Cleaning Laravel cache..."
    if [ -d "bootstrap/cache" ]; then
        rm -f bootstrap/cache/*.php
        echo "   - Removed cache files"
    fi
    
    echo "4. Running Laravel cache clear commands..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
    php artisan clear-compiled
    php artisan optimize:clear
    
    echo "5. Rebuilding autoload..."
    composer dump-autoload
fi

echo
echo "========================================"
echo "Uninstall process completed!"
echo "========================================"
echo
echo "Please verify that:"
echo "- Package is removed from composer.json"
echo "- All published files are removed"
echo "- Laravel application runs without errors"
echo
echo "If you encounter any issues, please:"
echo "1. Check the error messages above"
echo "2. Run: composer install"
echo "3. Run: php artisan optimize:clear"
echo "4. Create an issue on GitHub"
echo
