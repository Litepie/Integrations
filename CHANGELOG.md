# Changelog

All notable changes to this project will be documented in this file.

## [2.0.0] - 2025-08-26

### Changed
- **BREAKING**: Upgraded to Laravel 12.x support
- **BREAKING**: Minimum PHP version is now 8.2
- Updated Orchestra Testbench to v10.0 for Laravel 12 compatibility
- Updated PHPUnit to v11.0
- Updated PHPUnit configuration schema to v11.0

### Migration Notes
When upgrading from v1.x to v2.0:

1. **PHP Version**: Ensure your application is running PHP 8.2 or higher
2. **Laravel Version**: This package now requires Laravel 12.x
3. **Database Migrations**: All existing migrations have been merged into a single comprehensive migration file for new installations
4. **Testing**: If you're extending the test classes, ensure compatibility with PHPUnit 11

### Technical Details
- Consolidated database migrations into a single file for cleaner new installations
- All Laravel 12 compatibility verified across models, controllers, and service providers
- No breaking changes to the public API - all existing functionality remains unchanged

## [1.0.0] - 2024-01-01

### Added
- Initial release with Laravel 11 support
- OAuth integration management system
- Role-based permissions and access control
- IP and geographic restrictions
- Rate limiting capabilities
- Integration secrets management
- Comprehensive test suite
