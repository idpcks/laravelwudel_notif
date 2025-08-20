# Changelog

All notable changes to this project will be documented in this file.

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
