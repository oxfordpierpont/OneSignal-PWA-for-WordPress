# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2024-11-23

### Added
- Initial release of OneSignal PWA for WordPress
- Complete Progressive Web App implementation
  - Automatic manifest.json generation
  - Service worker with multiple caching strategies
  - Offline functionality
  - Installation prompts
  - App shortcuts support
- Full OneSignal integration
  - REST API client
  - Web SDK integration
  - Push notification management
  - User segmentation
  - Workflow automation
  - Analytics tracking
- Database schema for subscribers, notifications, segments, workflows, and analytics
- Settings management with encryption for API keys
- Icon processing and generation for all required sizes
- REST API endpoints for all major functionality
- WooCommerce integration
  - Order notifications
  - Cart abandonment recovery
  - Product back in stock alerts
  - Review requests
- Admin interface
  - Dashboard with analytics
  - Send notification interface
  - Subscriber management
  - Segment builder
  - Workflow builder
  - Analytics dashboard
  - PWA settings
  - General settings
- Public-facing features
  - Service worker registration
  - OneSignal initialization
  - Installation prompt
  - Event tracking
- Comprehensive documentation
- Uninstall cleanup functionality

### Security
- API key encryption using WordPress salts
- HTTPS requirement checking
- Nonce verification for all admin actions
- Input sanitization and validation
- Output escaping

### Performance
- Efficient database queries with proper indexing
- Caching support
- Lazy loading of admin assets
- Optimized service worker
- Minimal frontend footprint

## [Unreleased]

### Planned Features
- Easy Digital Downloads integration
- MemberPress integration
- LearnDash integration
- BuddyPress integration
- Additional workflow triggers
- Advanced A/B testing
- Machine learning features
- Multisite support
- White-label capabilities
