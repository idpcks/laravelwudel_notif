# Changelog

All notable changes to this project will be documented in this file.

## [1.0.5] - 2025-01-27

### Fixed
- **CRITICAL FIX**: Improved OpenSSL 3.0+ compatibility for VAPID key generation
- **CRITICAL FIX**: Better handling of OpenSSL 3.0+ stricter security policies
- **CRITICAL FIX**: Enhanced error messages and user guidance for OpenSSL 3.0+ users

### Added
- **NEW**: OpenSSL 3.0+ specific key generation method with legacy support
- **NEW**: Enhanced curve support detection without key generation
- **NEW**: Better fallback system for OpenSSL 3.0+ environments
- **NEW**: Comprehensive troubleshooting guide for OpenSSL 3.0+ issues
- **NEW**: OpenSSL 3.0+ compatibility test script (`examples/openssl3-test.php`)

### Improved
- **IMPROVED**: VAPID key generation now handles OpenSSL 3.0+ gracefully
- **IMPROVED**: Better error messages with specific solutions for OpenSSL 3.0+
- **IMPROVED**: Automatic detection and guidance for OpenSSL 3.0+ users
- **IMPROVED**: Enhanced documentation with OpenSSL 3.0+ solutions
- **IMPROVED**: More robust curve support checking

### Documentation
- **DOCS**: Added OpenSSL 3.0+ troubleshooting section
- **DOCS**: Enhanced installation guide with OpenSSL 3.0+ notes
- **DOCS**: Updated troubleshooting guide with specific solutions
- **DOCS**: Added OpenSSL 3.0+ compatibility information

### Compatibility
- **COMPATIBILITY**: Enhanced OpenSSL 3.0+ support
- **COMPATIBILITY**: Better handling of stricter security policies
- **COMPATIBILITY**: Improved fallback methods for various OpenSSL configurations

## [1.0.4] - 2025-01-27

### Fixed
- **CRITICAL FIX**: Resolved VAPID Keys Generator error on OpenSSL 3.0+
- **CRITICAL FIX**: Fixed "Call to undefined function openssl_ec_curve_nist_method()" error
- **CRITICAL FIX**: Fixed compatibility issues with Windows OpenSSL 3.0+

### Added
- **NEW**: Multiple fallback methods for VAPID key generation
- **NEW**: Sodium-based key generation as primary method (Method 5)
- **NEW**: 6 comprehensive fallback methods for maximum compatibility
- **NEW**: Cross-platform support (Windows, Linux, macOS)
- **NEW**: Comprehensive error handling and user feedback
- **NEW**: OpenSSL configuration file detection

### Improved
- **IMPROVED**: VAPID key generation now works on all OpenSSL versions
- **IMPROVED**: Better error messages and debugging information
- **IMPROVED**: Sequential fallback system for maximum success rate
- **IMPROVED**: User-friendly progress feedback during key generation
- **IMPROVED**: Production-ready implementation with security best practices

### Compatibility
- **COMPATIBILITY**: OpenSSL 1.1.x (all methods)
- **COMPATIBILITY**: OpenSSL 3.0+ (sodium-based method)
- **COMPATIBILITY**: Windows 10/11 with OpenSSL 3.0+
- **COMPATIBILITY**: PHP 7.4+ with sodium extension
- **COMPATIBILITY**: Laravel 8+ framework versions

### Security
- **SECURITY**: Sodium-based key generation for cryptographic security
- **SECURITY**: Memory cleanup after key generation
- **SECURITY**: Secure random key generation
- **SECURITY**: Production-ready VAPID key implementation

## [1.0.3] - 2025-01-27

## [1.0.3] - 2025-01-27

### Fixed
- **CRITICAL FIX**: Resolved fatal error during package uninstall
- **CRITICAL FIX**: Fixed "Service Provider Tidak Ditemukan" error
- **CRITICAL FIX**: Fixed cache corruption after uninstall
- **CRITICAL FIX**: Fixed Laravel application crash after package removal

### Added
- **NEW**: Automatic uninstall hook via composer `post-package-uninstall`
- **NEW**: `UninstallCommand` for manual package cleanup
- **NEW**: `EmergencyCleanupCommand` for critical cache corruption
- **NEW**: `CacheCleanupService` for robust cache management
- **NEW**: Uninstall scripts for Windows (`uninstall.bat`) and Unix (`uninstall.sh`)
- **NEW**: Comprehensive uninstall documentation (`UNINSTALL.md`)
- **NEW**: Automatic cache cleanup during uninstall
- **NEW**: Cache integrity validation
- **NEW**: Emergency cache reset functionality

### Improved
- **IMPROVED**: Service provider now handles uninstall gracefully
- **IMPROVED**: Cache management is now robust and automatic
- **IMPROVED**: Uninstall process is now foolproof
- **IMPROVED**: Error handling during uninstall
- **IMPROVED**: Documentation for troubleshooting uninstall issues

### Security
- **SECURITY**: Package now safely removes all traces during uninstall
- **SECURITY**: No more orphaned cache references
- **SECURITY**: Clean service provider removal

## [1.0.2] - 2025-01-27

### Added
- Initial release with web push notification features
- VAPID support
- Service worker integration
- Queue support for mass notifications
- Comprehensive testing suite

### Known Issues
- **CRITICAL**: Package uninstall causes fatal Laravel errors
- **CRITICAL**: Cache corruption after uninstall
- **CRITICAL**: Service provider not found errors
- **CRITICAL**: Manual cleanup required for proper uninstall

## [1.0.1] - 2025-01-20

### Added
- Basic web push notification functionality
- VAPID key generation
- Service worker implementation

## [1.0.0] - 2025-01-15

### Added
- Initial package structure
- Basic notification service
- Configuration files
