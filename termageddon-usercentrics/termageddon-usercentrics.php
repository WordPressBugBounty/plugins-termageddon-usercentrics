<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link    https://termageddon.com
 * @since   1.0.0
 * @package Termageddon_Usercentrics
 *
 * @wordpress-plugin
 * Plugin Name:       Termageddon
 * Description:       Each Termageddon license includes a consent solution. This plugin helps you install the consent solution with ease, while offering additional features.
 * Version:           1.8.1
 * Author:            Termageddon
 * Author URI:        https://termageddon.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       termageddon-usercentrics
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TERMAGEDDON_COOKIE_VERSION', '1.8.1' );

define( 'TERMAGEDDON_COOKIE_PLUGIN_PATH', dirname( __FILE__ ) );// No trailing slash.

if ( ! is_dir( TERMAGEDDON_COOKIE_PLUGIN_PATH ) ) {
	throw new Exception( 'Termageddon Plugin directory cannot be calculated.' );
}

define( 'TERMAGEDDON_COOKIE_EXEC_PATH', __FILE__ );// No trailing slash.

if ( ! file_exists( TERMAGEDDON_COOKIE_EXEC_PATH ) ) {
	throw new Exception( 'Termageddon Plugin File cannot be calculated.' );
}

$termageddon_path_info = pathinfo( TERMAGEDDON_COOKIE_EXEC_PATH );
define( 'TERMAGEDDON_COOKIE_EXEC_RELATIVE_PATH', basename( $termageddon_path_info['dirname'] ) . '/' . $termageddon_path_info['basename'] );// No trailing slash.

define( 'TERMAGEDDON_COOKIE_PATH', plugin_dir_path( __FILE__ ) );
define( 'TERMAGEDDON_COOKIE_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-termageddon-usercentrics-activator.php
 */
function activate_termageddon_cookie() {
	include_once TERMAGEDDON_COOKIE_PATH . 'includes/class-termageddon-usercentrics-activator.php';
	Termageddon_Usercentrics_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-termageddon-usercentrics-deactivator.php
 */
function deactivate_termageddon_cookie() {
	include_once TERMAGEDDON_COOKIE_PATH . 'includes/class-termageddon-usercentrics-deactivator.php';
	Termageddon_Usercentrics_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_termageddon_cookie' );
register_deactivation_hook( __FILE__, 'deactivate_termageddon_cookie' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require TERMAGEDDON_COOKIE_PATH . 'includes/class-termageddon-usercentrics.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_termageddon_cookie() {
	$plugin = new Termageddon_Usercentrics();
	$plugin->run();
}

// Hook the plugin initialization to plugins_loaded
add_action('init', 'run_termageddon_cookie');
