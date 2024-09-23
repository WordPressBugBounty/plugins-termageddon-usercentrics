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

		self::unregister_cron();
	}


	/**
	 * Register Cron if not registered
	 */
	protected static function unregister_cron() {

		if ( wp_next_scheduled( 'termageddon_usercentrics_maxmind_download' ) ) {
			wp_unschedule_hook( 'termageddon_usercentrics_maxmind_download' );
		}
	}

}
