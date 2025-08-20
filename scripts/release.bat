@echo off
REM Script untuk auto-release package Laravel (Windows)
REM Usage: release.bat [patch|minor|major]

setlocal enabledelayedexpansion

REM Check if version type is provided
if "%1"=="" (
    echo Usage: %0 [patch^|minor^|major]
    echo   patch: 1.0.0 -^> 1.0.1
    echo   minor: 1.0.0 -^> 1.1.0
    echo   major: 1.0.0 -^> 2.0.0
    exit /b 1
)

set VERSION_TYPE=%1

REM Get current version from composer.json (simple approach)
for /f "tokens=2 delims=:," %%i in ('findstr "version" composer.json') do (
    set CURRENT_VERSION=%%i
    set CURRENT_VERSION=!CURRENT_VERSION:"=!
    set CURRENT_VERSION=!CURRENT_VERSION: =!
)

if "!CURRENT_VERSION!"=="" (
    echo Version not found in composer.json, setting to 1.0.0
    set CURRENT_VERSION=1.0.0
)

echo Current version: !CURRENT_VERSION!

REM For Windows, we'll use a simpler approach
REM You may need to manually update version in composer.json

echo.
echo Please manually update the version in composer.json to the new version
echo Then run the following commands:
echo.
echo git add composer.json CHANGELOG.md
echo git commit -m "Bump version to [NEW_VERSION]"
echo git tag -a v[NEW_VERSION] -m "Release version [NEW_VERSION]"
echo git push origin main
echo git push origin v[NEW_VERSION]
echo.
echo Replace [NEW_VERSION] with the actual new version number
echo.
echo After pushing the tag, GitHub Actions will automatically:
echo 1. Create a release on GitHub: https://github.com/idpcks/laravelwudel_notif/releases
echo 2. Update Packagist: https://packagist.org/packages/laravelwudel/laravelwudel-notif
echo.
pause
