<?php
/**
 * Fired during plugin activation
 *
 * @link       https://termageddon.com
 * @since      1.0.0
 *
 * @package    Termageddon_Usercentrics
 * @subpackage Termageddon_Usercentrics/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Termageddon_Usercentrics
 * @subpackage Termageddon_Usercentrics/includes
 * @author     Termageddon <support@termageddon.com>
 */
class Termageddon_Usercentrics_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if ( ! class_exists( 'Termageddon_Usercentrics_Legacy_Geolocation_Cleanup' ) ) {
			require_once TERMAGEDDON_COOKIE_PATH . 'includes/class-termageddon-usercentrics-legacy-geolocation-cleanup.php';
		}
		Termageddon_Usercentrics_Legacy_Geolocation_Cleanup::maybe_run();

		do_action( 'termageddon-usercentrics/activated' );
	}

}
