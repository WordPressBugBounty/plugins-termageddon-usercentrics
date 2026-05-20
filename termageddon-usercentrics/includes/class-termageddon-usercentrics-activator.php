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

		// Ensure the geo-API helper class is available during activation; the main plugin
		// class only loads it via its constructor (which doesn't run during activation).
		if ( ! class_exists( 'Termageddon_Usercentrics_Geo_Api' ) ) {
			require_once TERMAGEDDON_COOKIE_PATH . 'includes/class-termageddon-usercentrics-geo-api.php';
		}

		self::maybe_set_geo_api_default_for_fresh_install();
		self::maybe_register_cron();
		Termageddon_Usercentrics::verify_maxmind_database();

		do_action( 'termageddon-usercentrics/activated' );
	}

	/**
	 * Fresh installs (no existing geoip or geo-API option) get opted into the new
	 * hosted geolocation service by default. Existing installs are unaffected — their
	 * absent option resolves to "off" and they keep the MaxMind path until they opt in.
	 *
	 * @return void
	 */
	protected static function maybe_set_geo_api_default_for_fresh_install() {
		$existing_geoip = get_option( 'termageddon_usercentrics_geoip_enabled', null );
		$existing_flag  = get_option( 'termageddon_use_geo_api', null );

		if ( null === $existing_geoip && null === $existing_flag ) {
			update_option( 'termageddon_use_geo_api', '1' );
		}
	}


	/**
	 * Register Cron if not registered.
	 *
	 * Skipped entirely when the new hosted geolocation service is enabled — there's no
	 * MaxMind database to keep up to date in that mode.
	 */
	protected static function maybe_register_cron() {

		if ( class_exists( 'Termageddon_Usercentrics_Geo_Api' ) && Termageddon_Usercentrics_Geo_Api::is_enabled() ) {
			return;
		}

		if ( ! wp_next_scheduled( 'termageddon_usercentrics_maxmind_download' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'termageddon_usercentrics_every_month', 'termageddon_usercentrics_maxmind_download' );
		}
	}

}
