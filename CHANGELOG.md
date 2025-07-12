# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Auto-update system via GitHub
- Comprehensive documentation

### Changed

- Improved error handling for phone field processing

### Fixed

- Phone numbers now properly save in Elementor form submissions
- Resolved issues with field validation hooks

## [1.0.0] - 2025-07-12

### Added

- Initial release
- International phone field with country selection
- Input masking based on selected country format
- Real-time phone number validation
- Full phone number storage in form submissions
- Customizable preferred countries
- Customizable initial country selection
- Mobile-responsive design
- Integration with Elementor Pro Forms
- Support for both public and private GitHub repositories
- Automatic plugin updates via GitHub releases
- Debug logging for troubleshooting

### Technical Details

- Uses intl-tel-input library for country selection
- Uses IMask library for input masking
- Implements WordPress plugin update API
- Supports PHP 7.4+ and WordPress 5.0+
- Compatible with latest Elementor Pro versions

### Known Issues

- None at this time

## [0.1.0] - 2025-07-12

### Added

- Basic phone field implementation
- Country selection dropdown
- Input masking functionality

### Issues Fixed in 1.0.0

- Phone numbers were not saving in submissions
- Validation hooks were not properly registered
- JavaScript conflicts with other plugins
