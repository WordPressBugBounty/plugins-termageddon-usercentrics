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

		self::set_hosted_geolocation_default();
		self::maybe_register_cron();
		Termageddon_Usercentrics::verify_maxmind_database();

		do_action( 'termageddon-usercentrics/activated' );
	}

	/**
	 * Hosted geolocation is the default. The temporary MaxMind fallback is an
	 * explicit opt-out managed from the Geolocation settings screen.
	 *
	 * @return void
	 */
	protected static function set_hosted_geolocation_default() {
		$existing_geoip    = get_option( 'termageddon_usercentrics_geoip_enabled', null );
		$migration_version = get_option( 'termageddon_geolocation_migration_version', null );

		// Activation can also mean a routine deactivate/reactivate cycle. Once the
		// migration has run, preserve the administrator's temporary fallback choice.
		if ( null !== $migration_version && (int) $migration_version >= 1 ) {
			return;
		}

		$maxmind_db_exists = file_exists( Termageddon_Usercentrics::get_maxmind_db_path() );
		$maxmind_scheduled = (bool) wp_next_scheduled( 'termageddon_usercentrics_maxmind_download' );

		update_option( 'termageddon_use_geo_api', '1' );
		update_option( Termageddon_Usercentrics_Geo_Api::LEGACY_MAXMIND_OPTION, '' );

		if ( null === $existing_geoip && null === $migration_version && ! $maxmind_db_exists && ! $maxmind_scheduled ) {
			update_option( 'termageddon_geolocation_migration_version', 1 );
			update_option( 'termageddon_maxmind_cleanup_complete', '1' );
			return;
		}

		Termageddon_Usercentrics::maybe_upgrade_geolocation();
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
