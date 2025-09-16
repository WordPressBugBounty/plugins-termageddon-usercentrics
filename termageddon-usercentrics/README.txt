=== Termageddon: Cookie Consent & Privacy Compliance ===
Contributors: termageddon, dintriglia
Tags: cookie consent, privacy, GDPR, CCPA, CPRA, CIPA, usercentrics, geolocation, compliance
Requires at least: 5.0
Tested up to: 6.8.1
Requires PHP: 7.2
Stable tag: 1.8.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The most comprehensive cookie consent solution for WordPress. Automatically show consent banners based on visitor location with smart geolocation targeting.

== Description ==

## TERMAGEDDON: CONSENT SOLUTION

This plugin is designed to help WordPress website owners quickly install the Termageddon consent solution onto their website. 

**Why choose Termageddon Cookie Consent?**

* **Smart Geolocation**: Automatically detect visitor locations and show consent banners only when required
* **More coverage than any other provider**: Termageddon covers privacy laws such as GDPR, CPRA, UK DPA, PIPEDA (Canada), Quebec 25, VCDPA, Australia Privacy Act, CIPA, and many, many more laws.
* **Lightning Fast**: AJAX-powered location detection maintains site speed and caching compatibility
* **Divi support**: Ensure the consent loads for end users, while preventing loading for logged in admins (to ensure Divi’s Visual Builder is unaffected).
* **Professional Support**: Dedicated support team ready to help

### 🌍 Global Privacy Law Support

Termageddon’s Auto-updating website policies and consent solution supports major privacy regulations worldwide:

* **🇪🇺 European Union & EEA** - GDPR
* **🇬🇧 United Kingdom** - UK DPA
* **🇨🇦 Canada** - PIPEDA & Quebec Law 25
* **🇺🇸 United States** - State-specific regulations:
  * California - CPRA, CalOPPA & CIPA
  * Colorado - CPA
  * Connecticut - CTDPA
  * Oregon - OCPA
  * Texas - TDPSA
  * Utah - UCPA
  * Virginia - VCDPA
  * And more.

### 🚀 Key Features

#### Smart Geolocation Targeting
* **MaxMind GeoLite2 Integration**: Accurate IP-based location detection
* **AJAX Mode**: Maintain site caching while ensuring accurate geolocation
* **Cookie Optimization**: Reduce server load with intelligent cookie-based location caching
* **Debug Mode**: Test and troubleshoot geolocation with built-in debugging tools

#### Seamless Integration
* **WordPress Integration**: Works with any WordPress theme and popular page builders
* **Usercentrics Powered**: Built on the industry-leading Usercentrics consent platform
* **Developer Friendly**: Extensive hooks, filters, and customization options
* **Performance Optimized**: Minimal impact on site speed and Core Web Vitals

#### Advanced Video Integrations
* **Divi Video**: Enhanced image overlay placeholder handling
* **Elementor Video**: Seamless consent integration with Elementor video widgets
* **PowerPack Video**: Support for BeaverBuilder PowerPack video embeds
* **Presto Player**: Optimized consent handling for Presto Player
* **Ultimate Addons**: Support for Beaver Builder Ultimate Addons

#### Privacy Settings Management
* **Flexible Shortcode**: `[uc-privacysettings]` with extensive customization options
* **Button & Link Support**: Choose between button or anchor elements
* **Custom Styling**: Full control over appearance with CSS targeting
* **Automatic Replacement**: Intelligent detection and replacement of privacy settings elements

### 📋 Shortcode Usage

Place privacy settings links anywhere on your site with the powerful shortcode:

`
[uc-privacysettings]
[uc-privacysettings text="Privacy Preferences"]
[uc-privacysettings type="button" text="Manage Cookies"]
`

**Supported Parameters:**
* `type` - Element type: "a" (default) or "button"
* `text` - Display text (default: "Privacy Settings")
* Styling via `#usercentrics-psl` CSS ID

### 🔧 Advanced Configuration

#### Provider Management
* **Disable Blocking**: Selectively disable cookie blocking for specific providers
* **Auto-Refresh**: Configure automatic page reload on consent for supported providers
* **Custom Integrations**: Extend functionality with custom provider configurations

#### Performance Optimization
* **CDN Configuration**: Optional CDN bypass for translations
* **Priority Control**: Adjust script loading priority for optimal performance
* **Cache Compatibility**: Full support for popular caching plugins
* **Troubleshooting Mode**: Disable for all users except when using `?enable-usercentrics`

### 🛡️ Privacy & Data Protection

**Important Privacy Notice**: When GeoIP is enabled, IP addresses are collected solely for determining appropriate consent requirements based on visitor location. A session cookie is created to improve performance on subsequent page loads. 

**Data Minimization**: All location data is processed temporarily and not stored permanently. Users can opt out by keeping all GeoIP checkboxes unchecked (default setting).

**Compliance First**: Ensure you are in compliance with all applicable privacy laws before installing this plugin or any tracking technologies.

== Installation ==

### Automatic Installation

1. Log in to your WordPress admin dashboard
2. Navigate to **Plugins → Add New**
3. Search for "Termageddon"
4. Click **Install Now** and then **Activate**
5. Go to **Tools → Termageddon + Usercentrics** to configure

### Manual Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/termageddon-usercentrics/` directory
3. Activate the plugin through the **Plugins** menu in WordPress
4. Configure via **Tools → Termageddon + Usercentrics**

### Quick Setup

After activation, follow these steps:

1. **Get Your Embed Code**: Log in to your Termageddon account, click into your license and click Embed Codes. Copy the Usercentrics SettingsID
2. **Paste & Configure**: Add the SettingsID into the plugin settings
3. **Enable Geolocation**: Choose which regions should see the consent banner
4. **Test & Go Live**: Use debug mode to test, then activate for all visitors

== Frequently Asked Questions ==

= Do I need a Termageddon account to use this plugin? =

Yes, this plugin requires a Termageddon license which includes the consent solution. The plugin helps you implement and optimize the consent solution with advanced features like geolocation targeting.

= How does geolocation work? =

The plugin uses MaxMind's GeoLite2 database to detect visitor locations based on IP addresses. Consent banners are shown only to visitors in jurisdictions that require them, improving user experience for others.

= Can I customize the appearance of the consent banner? =

Yes, the consent banner appearance is controlled through your Termageddon account settings. The plugin focuses on smart delivery and integration features. Setting the Banner to V3 (in the Settings area) offers full CSS control.

= Is this plugin compatible with caching plugins? =

Yes, the plugin includes AJAX mode specifically designed to work with caching plugins like WP Rocket, W3 Total Cache, WP Super Cache, and others. You may need to exclude usercentrics from JS modifications (available in [support article](https://termageddon.freshdesk.com/support/solutions/articles/66000503921-using-the-consent-solution-with-caching-optimization-tools-perfmatters-wp-rocket-nitropack-sg-siteground-optimizer-litespeed)).

= How do I add privacy settings links to my site? =

Use the `[uc-privacysettings]` shortcode anywhere on your site. You can customize the text and choose between button or link elements.

= What happens if I disable the plugin? =

The consent solution will stop loading, and privacy settings links will be hidden. Your site will no longer show consent banners to visitors.

= Can I test the geolocation features? =

Yes, enable debug mode in the plugin settings and use URL parameters like `?termageddon-usercentrics-debug=california` to test different locations.

= Is customer support available? =

Yes, comprehensive support is available through our dedicated support portal at [https://termageddon.freshdesk.com/](https://termageddon.freshdesk.com/).

== Screenshots ==

1. **Plugin Settings Dashboard** - Clean, intuitive interface for managing all consent settings
2. **Geolocation Configuration** - Easy setup for region-specific consent requirements  
3. **Integration Settings** - Advanced options for video players and third-party integrations
4. **Debug Mode** - Built-in testing tools for troubleshooting geolocation
5. **Privacy Settings Shortcode** - Flexible shortcode options for privacy settings links
6. **Performance Optimization** - Advanced settings for caching and performance tuning

== Support ==

For comprehensive support and assistance:

* **Help Center**: Visit our [support portal](https://termageddon.freshdesk.com/) for detailed documentation
* **Community Forum**: Get help from other users on WordPress.org support forums
* **Priority Support**: Termageddon customers receive priority email support
* **Developer Resources**: Access our developer documentation for advanced customizations

== Changelog ==

= 1.8.1 =

**🔧 Improvements:**
* Minor documentation improvement within the settings showcasing the button alternative.

= 1.8.0 =

**✨ New Features:**
* Added `type="button"` variant for privacy settings shortcode

**🔧 Improvements:**
* Upgraded GeoIP library. Minimum PHP version is now 7.2.
* Enhanced script processing for complex embed codes
* Enhanced accessibility for privacy settings links
* Improved script tag filtering for better embed code compatibility

**🐛 Bug Fixes:**
* Fixed issue where certain `<script>` tags were filtered from embed code
* Resolved consent solution flashing with geolocation enabled

= 1.7.2 =

**🐛 Bug Fixes:**
* Fixed consent solution flashing when using geolocation targeting
* Improved smooth loading experience for geo-targeted consent

= 1.7.1 =

**✨ New Features:**
* Added CDN disable toggle for translations
* Enhanced compatibility with LiteSpeed caching systems
* Expanded priority range for embed script positioning

**🔧 Improvements:**
* Added `data-no-optimize` flags for better caching plugin compatibility
* Geo-location debug code now consistently loads in footer
* Enhanced error handling for `load_plugin_textdomain` function

**🐛 Bug Fixes:**
* Fixed issues with `wp_enqueue_scripts` injection method
* Resolved deprecation notice for `load_plugin_textdomain`

= 1.7.0 =

**✨ New Features:**
* Advanced Configuration section in Integrations tab
* Auto-refresh on consent for supported providers
* Deactivate blocking for specific providers
* Reorganized admin interface for better user experience

**🔧 Improvements:**
* Enhanced provider management capabilities
* Streamlined admin tab organization
* Better workflow for advanced users

= 1.6.2 =

**🐛 Bug Fixes:**
* Fixed WordPress 6.7+ compatibility issue causing deprecation notices
* Improved error handling for newer WordPress versions

= 1.6.1 =

**🔧 Improvements:**
* Added WordPress 6.7.2 compatibility
* Updated plugin branding to Termageddon-specific styling
* Enhanced overall plugin presentation

= 1.6.0 =

**✨ New Features:**
* Usercentrics Consent API V3 support with improved embed code
* Alternative injection method for enhanced implementation flexibility
* Settings ID implementation replacing traditional embed code approach
* One-click conversion tool for existing installations
* PowerPack Video integration for BeaverBuilder
* Ultimate Addons for Beaver Builder Video support

**🔧 Improvements:**
* Modernized consent implementation architecture
* Enhanced compatibility with various WordPress setups
* Streamlined configuration process

= 1.5.4 =

**🐛 Bug Fixes:**
* Fixed composer autoloader compatibility issues for older WordPress installations
* Improved backward compatibility for legacy sites

= 1.5.2 =

**🐛 Bug Fixes:**
* Resolved Elementor compatibility toggle syntax error
* Enhanced error handling for integration settings

= 1.5.1 =

**🐛 Bug Fixes:**
* Fixed fatal error when Geolocation features were disabled
* Improved error handling for geolocation-dependent functionality

= 1.5.0 =

**✨ New Features:**
* Expanded US state support for new privacy laws:
  * Colorado Consumer Privacy Act (CPA)
  * Virginia Consumer Data Protection Act (VCDPA)  
  * Connecticut Data Privacy Act (CTDPA)
  * Oregon Consumer Privacy Act (OCPA)
  * Texas Data Privacy and Security Act (TDPSA)
  * Utah Consumer Privacy Act (UCPA)

**🔧 Improvements:**
* Enhanced geolocation targeting for US state-specific regulations
* Updated compliance framework for emerging privacy laws

= 1.4.5 =

**✨ New Features:**
* Menu integration support with `usercentrics-psl` class
* Automatic conversion of menu items to privacy settings links

**🔧 Improvements:**
* Enhanced flexibility for privacy settings link placement
* Better integration with WordPress menu systems

= 1.4.4 =

**🐛 Bug Fixes:**
* General bug fixes and stability improvements
* Enhanced error handling and performance optimizations

= 1.4.3 =

**✨ New Features:**
* Custom embed code priority control
* Enhanced script loading order management

**🐛 Bug Fixes:**
* Fixed rare geolocation activation crash
* Improved error handling for edge cases

= 1.4.2 =

**✨ New Features:**
* Elementor Video integration support
* Enhanced image overlay placeholder handling for Elementor video widgets

**🔧 Improvements:**
* Better third-party page builder compatibility
* Enhanced consent handling for video content

= 1.4.1 =

**🐛 Bug Fixes:**
* Fixed AJAX mode cache busting issue with Pressable and similar providers
* Improved caching compatibility for managed WordPress hosting

= 1.4.0 =

**✨ New Features:**
* Troubleshooting mode for debugging consent issues
* Plugin disable functionality with `?enable-usercentrics` override
* Enhanced debugging capabilities for complex setups

**🔧 Improvements:**
* Better troubleshooting workflow for administrators
* Enhanced debugging tools for consent delivery issues

= 1.3.9 =

**🔧 Improvements:**
* Removed jQuery dependency for privacy settings links
* Enhanced performance by reducing JavaScript dependencies
* Better compatibility with jQuery-free WordPress themes

= 1.3.8 =

**✨ New Features:**
* California Consumer Privacy Act (CIPA) support
* Divi Video integration for enhanced video consent handling
* Comprehensive geolocation documentation

**🔧 Improvements:**
* Enhanced California privacy law compliance
* Better video player integration capabilities

= 1.3.7 =

**✨ New Features:**
* WordPress 6.4.2 compatibility
* Usercentrics `data-version` attribute support in embed codes

**🔧 Improvements:**
* Enhanced embed code flexibility
* Better version management for Usercentrics integrations

= 1.3.6 =

**🔧 Improvements:**
* Enhanced `data-usercentrics` attribute support in embed codes
* Better compatibility with complex embed configurations

**🐛 Bug Fixes:**
* Fixed MaxMind GeoIP2 library conflict with other plugins
* Improved library loading and conflict resolution

= 1.3.5 =

**✨ New Features:**
* Persistent geolocation settings when toggling features
* Enhanced user experience for geolocation management

**🐛 Bug Fixes:**
* Fixed PHP 8 deprecation warning
* Improved PHP version compatibility

= 1.3.4 =

**✨ New Features:**
* Alternative privacy settings link implementation (Divi support)
* Dedicated privacy settings link configuration section
* Enhanced element hiding with `usercentrics-psl` class

**🔧 Improvements:**
* Moved privacy settings options to dedicated Settings page
* Enhanced WP_CLI compatibility and error handling

= 1.3.3 =

**✨ New Features:**
* Presto Player integration for enhanced video consent handling

**🔧 Improvements:**
* Better video player compatibility across different platforms
* Enhanced consent handling for embedded video content

= 1.3.2 =

**✨ New Features:**
* Virginia Consumer Data Protection Act (VCDPA) geolocation support

**🔧 Improvements:**
* Enhanced US state-specific privacy law compliance
* Better regional targeting for consent requirements

= 1.3.1 =

**🐛 Bug Fixes:**
* Fixed potential wp_cron geolocation database download issue
* Improved scheduled task reliability

= 1.3.0 =

**🔧 Improvements:**
* Enhanced geolocation database error handling
* Better reliability for location-based consent delivery

**🐛 Bug Fixes:**
* Fixed duplicate geolocation database update scheduling
* Improved plugin reactivation handling

= 1.2.4 =

**🔧 Improvements:**
* Updated terminology from CCPA to CPRA for accuracy
* WordPress 6.1.1 compatibility verification

= 1.2.3 =

**✨ New Features:**
* Quick Settings link on plugins page for easier access

**🐛 Bug Fixes:**
* Fixed WordPress default settings saving conflict
* Improved plugin settings isolation

= 1.2.2 =

**🐛 Bug Fixes:**
* Fixed privacy settings link to force-show consent widget
* Improved widget visibility handling with geolocation

= 1.2.1 =

**✨ New Features:**
* Privacy Settings Link shortcode with toggle controls
* Tabbed admin interface for better organization
* Enhanced frontend UI for incompatible option handling

**🔧 Improvements:**
* Streamlined admin dashboard design
* Better user experience for configuration management
* Automatic option compatibility checking

= 1.2.0 =

**✨ New Features:**
* AJAX caching method for geolocation compatibility
* Enhanced support for website caching systems
* Better performance with cached websites

**🔧 Improvements:**
* Significant performance improvements for cached sites
* Better compatibility with popular caching plugins

= 1.1.4 =

**✨ New Features:**
* Admin location logging toggle for geolocation troubleshooting
* Browser console location display for debugging
* Enhanced testing capabilities for administrators

= 1.1.3 =

**🐛 Bug Fixes:**
* Fixed "disable for admin users" functionality
* Improved administrator experience during development

= 1.1.2 =

**✨ New Features:**
* Link display option as alternative to button format
* Automatic element hiding with `usercentrics-psl` class
* Enhanced privacy settings link management

**🔧 Improvements:**
* Better CSS-based element visibility control
* Enhanced integration with theme designs

= 1.1.1 =

**🐛 Bug Fixes:**
* Fixed fatal error affecting certain WordPress installations
* Improved plugin compatibility and stability

= 1.1.0 =

**✨ New Features:**
* Advanced geolocation targeting system
* Location-based consent banner display
* MaxMind GeoLite2 database integration
* Regional compliance automation

**🔧 Improvements:**
* Intelligent consent delivery based on visitor location
* Enhanced user experience for non-regulated regions
* Comprehensive geolocation management interface

= 1.0.0 =

**✨ Initial Release:**
* Core Termageddon + Usercentrics integration
* Basic embed script compatibility
* User authentication toggles
* Foundation for advanced geolocation features

**🔧 Core Features:**
* Seamless WordPress integration
* Usercentrics consent platform integration
* Administrative control panel
* Basic consent delivery system

== Upgrade Notice ==

= 1.7.0 =
Significant update with advanced configuration options for provider management and auto-refresh capabilities. Enhanced admin interface organization.

= 1.6.0 =
Major update introducing Usercentrics Consent API V3 support, Settings ID implementation, and new video integration options. One-click conversion available for existing installations.

= 1.5.0 =
Important update adding support for new US state privacy laws including Colorado, Virginia, Connecticut, Oregon, Texas, and Utah. Enhanced geolocation targeting capabilities.

= 1.2.0 =
Major performance update introducing AJAX caching method for improved compatibility with caching plugins and better site performance.

= 1.1.0 =
Significant update adding advanced geolocation features with MaxMind GeoLite2 integration for location-based consent delivery.

= 1.0.0 =
Initial release of the Termageddon Cookie Consent plugin with core Usercentrics integration and WordPress compatibility.
