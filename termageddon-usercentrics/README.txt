=== Plugin Name ===
Contributors: dintriglia
Donate link: https://termageddon.com
Tags: termageddon, cookie, consent, embed, usercentrics
Requires at least: 3.0.1
Tested up to: 6.7
Stable tag: 1.5.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily integrate the UserCentrics consent solution into your website while controlling visibility for logged in users and admins.

== Description ==

Easily integrate the UserCentrics consent solution into your website.

This plugin also allows for hiding the consent solution if the user is logged in as an admin.

As of v1.1.0, the plugin can use the [MaxMind GeoLite2](https://dev.maxmind.com/geoip/geolite2-free-geolocation-data?lang=en) library to automatically estimate the location of your site's users. Based on that location, the consent solution can only be shown if necessary to that end-user.

As of v1.2.0, the plugin can check the visitor's location via AJAX to allow the website to still be cached for speed, while also ensuring accurate results for displaying the consent widget.

If you wish to place a privacy settings link in the footer or anywhere else, you can use the [uc-privacysettings] shortcode.

**Please note**: When GeoIP is enabled, you will be collecting IP addresses for the purposes of determining which cookie consent solution (or lack thereof) to provide to each website visitor (CPRA or CIPA cookie consent, GDPR cookie consent, UK DPA consent or none), based on their location. A cookie will then be placed on the user's browser to log their session, which helps improve page load speed when the user visits other pages on the website. You should ensure that you are in compliance with all applicable privacy laws prior to installing this plugin (or any other technologies on your website). To opt out of this feature, keep all GeoIP checkboxes unchecked (default).

== Changelog ==

= 1.5.4 =

-   [FIX] Fixed an issue in which older sites were not compatible with the composer autoloader.

= 1.5.2 =

-   [FIX] Fixed an issue in which a syntax error would occur with the Elementor compatibility toggle.

= 1.5.1 =

-   [FIX] Fixed an issue in which the plugin would present a fatal error when Geolocation was disabled.

= 1.5.0 =

-   [ADD] New states are now requiring cookie consent forms. The plugin now supports additional locations: Colorado, Virginia, Connecticut, Oregon, Texas, Utah

= 1.4.5 =

-   [ADD] Added support for using menus with the `usercentrics-psl` class that will be converted to privacy settings links.

= 1.4.4 =

-   [FIX] Bug fixes

= 1.4.3 =

-   [ADD] Added Custom Embed Code Priority (Under Settings) option to allow for more control over the priority of the embed script. 
-   [FIX] Fixed a rare issue where crash on enabling geolocation.

= 1.4.2 =

-   [ADD] Added Elementor Video Integration Support to improve image overlay placeholder handling when consent is required. 

= 1.4.1 =

-   [FIX] Fixed an issue where the plugin with Ajax Mode would bust cache for every request for some providers such as Pressable.

= 1.4.0 =

-   [ADD] Added ability to turn off plugin for troubleshooting to all visitors unless ?enable-usercentrics is added as a query parameter to the URL.

= 1.3.9 =

-   [CHANGE] Removed jQuery as requirement for privacy settings link.
-   [Change] Enqueued jQuery where required for other implementations to avoid a missing dependency.

= 1.3.8 =

-   [ADD] Added support for CIPA in California.
-   [ADD] Added Divi Video Integration Support to improve image overlay placeholder handling when consent is required.
-   [ADD] Added documentation link for Geo-Location.

= 1.3.7 =

-   [ADD] Added support for WordPress v6.4.2
-   [ADD] Added support for usercentrics data-version attribute in embed code.

= 1.3.6 =

-   [CHANGE] Improved support for data-usercentrics attributes in the Embed Code textarea field.
-   [FIX] Fixed a fatal error that occurs if other plugins are using MaxMind Geoip2 library.

= 1.3.5 =

-   [ADD] When disabling geolocation, and then re-enabling it in the future, it will now keep your settings so you do not have to reset it up each time.
-   [FIX] Fixed a Deprectated PHP warning being thrown for PHP8.

= 1.3.4 =

-   [ADD] Added alternate privacy settings link embed as an alternative for shortcodes (Divi Support).
-   [ADD] Added settings section to showcase privacy settings link settings.
-   [CHANGE] Moved Hide Privacy Settings Link option to Settings page instead of geolocation page.
-   [FIX] Improved handling for WP_CLI actions and error logging.

= 1.3.3 =

-   [ADD] Added integration to improve support with Presto Player.

= 1.3.2 =

-   [ADD] Added geolocation opt-in support for Virginia to allow for compliance with VCDPA.

= 1.3.1 =

-   [FIX] Fixed potential issue with wp_cron that could an issue downloading geolocation database.

= 1.3.0 =

-   [CHANGE] Updated geolocation database for improved error handling.
-   [FIX] Fixed an issue causing duplicate geolocation database update scheduling on re-activation of plugin.

= 1.2.4 =

-   [CHANGE] Updated verbiage from CCPA to CPRA.
-   [CHANGE] WordPress compatibility for 6.1.1.

= 1.2.3 =

-   [FIX] Fixed an issue in which users could not save default wordpress settings when using the plugin.
-   [ADD] Add quick “Settings” link on the plugins page to quickly jump to the settings panel for the plugin.

= 1.2.2 =

-   [FIX] Fixed Privacy Settings Link to force show widget even if hidden via geo-location

= 1.2.1 =

-   [ADD] Added Privacy Settings Link shortcode and toggleability to ensure it does not dissapear unless you want it to.
-   [CHANGE] Implemented tabs to improve usability and cleanliness of the dashboard.
-   [CHANGE] Implemented new UI to allow easier navigation and understanding how the options work.
-   [CHANGE] Improved frontend to ensure incompatible options are automatically updated.

= 1.2.0 =

-   Implemented AJAX caching method for geolocation to allow support for various website caching systems.

= 1.1.4 =

-   Allow admins to enable location logging to troubleshoot and test the geo-location options. When enabled, the current location extrapolated via the plugin will be shown in the browser console.

= 1.1.3 =

-   Fix an issue in which disable for admin users was not working

= 1.1.2 =

-   Added support for link instead of button display option. If termageddon disabled, the link will also automatically be hidden via CSS.
-   Added support for elements to be hidden automatically when usercentrics is disabled using the "usercentrics-psl" class. If termageddon is disabled, anything tagged with that class will also be hidden.

= 1.1.1 =

-   Fixed Fatal error affecting some sites.

= 1.1.0 =

-   Added Geo-location options. Added support for contingently showing the consent banner if the user is located in a location that REQUIRES the banner to be shown. If not, cookie consent is bypassed.
-   Added geo-location options for toggleability based on user location. If disabled, geoIP is disabled.

= 1.0 =

-   Initial Integration
-   Added options for embed script compatibility and toggleability for if a user is logged in.
