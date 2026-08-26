<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://termageddon.com
 * @since      1.0.0
 *
 * @package    Termageddon_Usercentrics
 * @subpackage Termageddon_Usercentrics/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Termageddon_Usercentrics
 * @subpackage Termageddon_Usercentrics/includes
 * @author     Termageddon <support@termageddon.com>
 */
class Termageddon_Usercentrics_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		if ( ! class_exists( 'Termageddon_Usercentrics_Legacy_Geolocation_Cleanup' ) ) {
			require_once TERMAGEDDON_COOKIE_PATH . 'includes/class-termageddon-usercentrics-legacy-geolocation-cleanup.php';
		}
		Termageddon_Usercentrics_Legacy_Geolocation_Cleanup::maybe_run();
	}

}
