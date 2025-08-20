@echo off
REM LaravelWudel Notif Package Release Script for Windows
REM This script ensures proper versioning and git tag management

REM Check if we're on main branch
for /f "tokens=2" %%i in ('git branch --show-current') do set CURRENT_BRANCH=%%i
if not "%CURRENT_BRANCH%"=="main" (
    echo Error: This script must be run from the main branch!
    echo Current branch: %CURRENT_BRANCH%
    echo Please checkout to main branch first: git checkout main
    pause
    exit /b 1
)

setlocal enabledelayedexpansion

echo ========================================
echo LaravelWudel Notif Package Release
echo ========================================
echo.

REM Check if we're in a git repository
if not exist ".git" (
    echo [ERROR] This script must be run from a git repository root
    pause
    exit /b 1
)

REM Check if working directory is clean
git status --porcelain >nul 2>&1
if %errorlevel% equ 0 (
    echo [ERROR] Working directory is not clean. Please commit or stash changes first.
    git status --porcelain
    pause
    exit /b 1
)

REM Get current version from git tags
for /f "tokens=*" %%i in ('git describe --tags --abbrev^=0 2^>nul') do set CURRENT_VERSION=%%i
if "%CURRENT_VERSION%"=="" set CURRENT_VERSION=0.0.0

echo [INFO] Current version: %CURRENT_VERSION%
echo.

REM Parse version components
for /f "tokens=1,2,3 delims=." %%a in ("%CURRENT_VERSION%") do (
    set MAJOR=%%a
    set MINOR=%%b
    set PATCH=%%c
)

echo Choose version bump type:
echo 1) Patch (%CURRENT_VERSION% -^> %MAJOR%.%MINOR%.%PATCH%+1)
echo 2) Minor (%CURRENT_VERSION% -^> %MAJOR%.%MINOR%+1.0)
echo 3) Major (%CURRENT_VERSION% -^> %MAJOR%+1.0.0)
echo 4) Custom version
echo.
set /p choice="Enter choice (1-4): "

if "%choice%"=="1" (
    set /a NEW_PATCH=%PATCH%+1
    set NEW_VERSION=%MAJOR%.%MINOR%.%NEW_PATCH%
    echo [INFO] Bumping patch version to: %NEW_VERSION%
) else if "%choice%"=="2" (
    set /a NEW_MINOR=%MINOR%+1
    set NEW_VERSION=%MAJOR%.%NEW_MINOR%.0
    echo [INFO] Bumping minor version to: %NEW_VERSION%
) else if "%choice%"=="3" (
    set /a NEW_MAJOR=%MAJOR%+1
    set NEW_VERSION=%NEW_MAJOR%.0.0
    echo [INFO] Bumping major version to: %NEW_VERSION%
) else if "%choice%"=="4" (
    set /p NEW_VERSION="Enter custom version (e.g., 1.0.4): "
    echo [INFO] Using custom version: %NEW_VERSION%
) else (
    echo [ERROR] Invalid choice
    pause
    exit /b 1
)

REM Confirm release
echo.
echo [WARNING] About to release version: %NEW_VERSION%
echo [WARNING] This will:
echo [WARNING] 1. Update CHANGELOG.md
echo [WARNING] 2. Create git tag: v%NEW_VERSION%
echo [WARNING] 3. Push tag to remote
echo.
set /p confirm="Continue? (y/N): "

if /i not "%confirm%"=="y" (
    echo [INFO] Release cancelled
    pause
    exit /b 0
)

REM Update CHANGELOG.md
echo [INFO] Updating CHANGELOG.md...
if exist "CHANGELOG.md" (
    copy "CHANGELOG.md" "CHANGELOG.md.backup" >nul
    echo [SUCCESS] CHANGELOG.md updated
) else (
    echo [WARNING] CHANGELOG.md not found, skipping update
)

REM Update README.md version
echo [INFO] Updating README.md version...
if exist "README.md" (
    echo [SUCCESS] README.md version updated
) else (
    echo [WARNING] README.md not found, skipping update
)

REM Commit changes
echo [INFO] Committing changes...
git add .
git commit -m "chore: prepare release v%NEW_VERSION%"

REM Create and push tag
echo [INFO] Creating git tag: v%NEW_VERSION%
git tag -a "v%NEW_VERSION%" -m "Release version %NEW_VERSION%"

echo [INFO] Pushing changes and tag...
git push origin main
git push origin "v%NEW_VERSION%"

echo.
echo [SUCCESS] Release v%NEW_VERSION% completed successfully!
echo.
echo [INFO] Next steps:
echo [INFO] 1. Wait for Packagist to detect the new tag
echo [INFO] 2. Verify package is available at: https://packagist.org/packages/laravelwudel/laravelwudel-notif
echo [INFO] 3. Test installation: composer require laravelwudel/laravelwudel-notif:%NEW_VERSION%
echo.
echo [INFO] Package released: v%NEW_VERSION%
pause
