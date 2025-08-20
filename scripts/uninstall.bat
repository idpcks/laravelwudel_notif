@echo off
echo ========================================
echo LaravelWudel Notif Package Uninstaller
echo ========================================
echo.

echo Starting uninstall process...
echo.

REM Check if Laravel is available
php artisan --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Laravel not found or not accessible
    echo Please run this script from your Laravel project root directory
    pause
    exit /b 1
)

echo 1. Running Laravel uninstall command...
php artisan laravelwudel-notif:uninstall --force

if %errorlevel% neq 0 (
    echo.
    echo WARNING: Laravel command failed, performing manual cleanup...
    echo.
    
    echo 2. Removing published files...
    if exist "app\Models\PushSubscription.php" (
        del /f "app\Models\PushSubscription.php"
        echo    - Removed PushSubscription model
    )
    
    if exist "config\laravelwudel-notif.php" (
        del /f "config\laravelwudel-notif.php"
        echo    - Removed config file
    )
    
    if exist "resources\views\vendor\laravelwudel-notif" (
        rmdir /s /q "resources\views\vendor\laravelwudel-notif"
        echo    - Removed views directory
    )
    
    if exist "public\vendor\laravelwudel-notif" (
        rmdir /s /q "public\vendor\laravelwudel-notif"
        echo    - Removed assets directory
    )
    
    echo 3. Cleaning Laravel cache...
    if exist "bootstrap\cache" (
        del /f "bootstrap\cache\*.php"
        echo    - Removed cache files
    )
    
    echo 4. Running Laravel cache clear commands...
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
    php artisan clear-compiled
    php artisan optimize:clear
    
    echo 5. Rebuilding autoload...
    composer dump-autoload
)

echo.
echo ========================================
echo Uninstall process completed!
echo ========================================
echo.
echo Please verify that:
echo - Package is removed from composer.json
echo - All published files are removed
echo - Laravel application runs without errors
echo.
echo If you encounter any issues, please:
echo 1. Check the error messages above
echo 2. Run: composer install
echo 3. Run: php artisan optimize:clear
echo 4. Create an issue on GitHub
echo.
pause
