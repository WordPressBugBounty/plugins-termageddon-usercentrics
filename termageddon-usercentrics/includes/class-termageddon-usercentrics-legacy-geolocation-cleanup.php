<?php
/**
 * One-time cleanup for the removed on-device geolocation implementation.
 *
 * This class is intentionally isolated so it can be deleted in a future release
 * after installations have had sufficient time to remove legacy files and state.
 *
 * @package Termageddon_Usercentrics
 */

/**
 * Remove files, scheduled events, options, and user metadata left by MaxMind.
 */
class Termageddon_Usercentrics_Legacy_Geolocation_Cleanup {

	/**
	 * Cleanup schema version.
	 *
	 * @var int
	 */
	private const CLEANUP_VERSION = 2;

	/**
	 * Run cleanup once, retrying on later requests if the database cannot be removed.
	 *
	 * @return bool True when all material legacy state has been removed.
	 */
	public static function maybe_run(): bool {
		if ( self::CLEANUP_VERSION <= (int) get_option( 'termageddon_legacy_geolocation_cleanup_version', 0 ) ) {
			return true;
		}

		$upload_dir = wp_upload_dir();
		$uploads_base_dir = isset( $upload_dir['basedir'] ) && is_string( $upload_dir['basedir'] )
			? rtrim( $upload_dir['basedir'], '/\\' )
			: '';

		// Never derive a deletion target from an empty, relative, or filesystem-root path.
		$is_absolute_path = '/' === substr( $uploads_base_dir, 0, 1 )
			|| '\\' === substr( $uploads_base_dir, 0, 1 )
			|| 1 === preg_match( '/^[A-Za-z]:[\\\\\/]/', $uploads_base_dir );
		$is_root_path = '' === $uploads_base_dir
			|| '/' === $uploads_base_dir
			|| '\\' === $uploads_base_dir
			|| 1 === preg_match( '/^[A-Za-z]:$/', $uploads_base_dir );
		if ( ! $is_absolute_path || $is_root_path ) {
			return false;
		}

		$legacy_dir = $uploads_base_dir . DIRECTORY_SEPARATOR . 'termageddon-maxmind';

		// Old releases stored the MaxMind database in this plugin-owned directory.
		// Delete the directory, rather than only the expected database name, so a
		// skipped upgrade also removes interrupted downloads and other legacy files.
		$files_removed = self::remove_legacy_directory( $legacy_dir );

		foreach (
			array(
				'termageddon_usercentrics_maxmind_download',
				'termageddon_usercentrics_maxmind_sunset',
			) as $hook
		) {
			wp_unschedule_hook( $hook );
		}

		$legacy_options = array(
			'termageddon_use_geo_api',
			'termageddon_use_legacy_maxmind',
			'termageddon_maxmind_cleanup_complete',
			'termageddon_geolocation_migration_version',
			'termageddon_geolocation_migration_notice',
			'termageddon_usercentrics_download_error_count',
			'termageddon_usercentrics_download_error_log',
			'termageddon_usercentrics_location_ajax',
		);
		foreach ( $legacy_options as $option ) {
			delete_option( $option );
		}

		$options_removed = true;
		foreach ( $legacy_options as $option ) {
			$not_found = new stdClass();
			if ( $not_found !== get_option( $option, $not_found ) ) {
				$options_removed = false;
				break;
			}
		}

		if ( function_exists( 'delete_metadata' ) ) {
			delete_metadata( 'user', 0, 'termageddon_geo_migration_notice_dismissed', '', true );
		}

		$cleanup_complete = $files_removed
			&& $options_removed
			&& ! file_exists( $legacy_dir )
			&& ! is_link( $legacy_dir )
			&& ! wp_next_scheduled( 'termageddon_usercentrics_maxmind_download' )
			&& ! wp_next_scheduled( 'termageddon_usercentrics_maxmind_sunset' );

		if ( $cleanup_complete ) {
			update_option( 'termageddon_legacy_geolocation_cleanup_version', self::CLEANUP_VERSION );
		}

		return $cleanup_complete;
	}

	/**
	 * Remove the dedicated legacy MaxMind directory and all of its contents.
	 *
	 * @param string $directory Absolute directory path under WordPress uploads.
	 * @return bool True when the directory is absent after the attempt.
	 */
	private static function remove_legacy_directory( string $directory ): bool {
		if ( ! file_exists( $directory ) && ! is_link( $directory ) ) {
			return true;
		}

		if ( ! is_dir( $directory ) || is_link( $directory ) ) {
			return @unlink( $directory ); // phpcs:ignore WordPress.PHP.NoSilencedErrors -- legacy file or symlink cleanup.
		}

		try {
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $directory, FilesystemIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			);
			foreach ( $files as $file ) {
				$path = $file->getPathname();
				if ( $file->isDir() && ! $file->isLink() ) {
					if ( ! @rmdir( $path ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors -- retry on a later request.
						return false;
					}
				} elseif ( ! @unlink( $path ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors -- retry on a later request.
					return false;
				}
			}
		} catch ( UnexpectedValueException $exception ) {
			return false;
		}

		return @rmdir( $directory ); // phpcs:ignore WordPress.PHP.NoSilencedErrors -- retry on a later request.
	}
}
