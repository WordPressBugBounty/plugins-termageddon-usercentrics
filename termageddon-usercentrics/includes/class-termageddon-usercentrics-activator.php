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

		self::maybe_register_cron();
		Termageddon_Usercentrics::verify_maxmind_database();

		do_action( 'termageddon-usercentrics/activated' );
	}


	/**
	 * Register Cron if not registered
	 */
	protected static function maybe_register_cron() {

		if ( ! wp_next_scheduled( 'termageddon_usercentrics_maxmind_download' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'termageddon_usercentrics_every_month', 'termageddon_usercentrics_maxmind_download' );
		}
	}

}
