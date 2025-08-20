# Changelog

All notable changes to the `laravelwudel/laravelwudel-notif` package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of LaravelWudel Notif package
- Web Push Notifications with VAPID support (Custom Implementation)
- **100% Custom Code** - No external library dependencies
- Custom VAPID key generation using OpenSSL
- Custom web push service implementation
- Custom JWT signing for VAPID authentication
- Service Provider auto-discovery
- Facade for easy usage
- Model and Migration for push subscriptions
- Queue support for mass notifications
- Error handling and logging
- Testing support
- Console commands for VAPID key generation and subscription cleanup
- Asset publishing for JavaScript and Service Worker files

### Changed
- **Removed dependency on minishlink/web-push**
- Implemented custom web push notification service
- Custom branding: LaravelWudel Notif

### Removed
- Complex polymorphic relationships
- Unused configuration options
- Overly complex service methods

## [1.0.0] - 2025-08-20

### Added
- Initial release
- Basic push notification functionality
- VAPID key generation
- Subscription management
- Notification sending to users, topics, and all users
- Package published to Packagist.org
- Composer installation support

## Support

For issues and questions, please create an issue in the [GitHub repository](https://github.com/idpcks/laravelwudel_notif) or contact the LaravelWudel team.

## Links

- **GitHub Repository**: [https://github.com/idpcks/laravelwudel_notif](https://github.com/idpcks/laravelwudel_notif)
- **Packagist Package**: [https://packagist.org/packages/laravelwudel/laravelwudel-notif](https://packagist.org/packages/laravelwudel/laravelwudel-notif)
