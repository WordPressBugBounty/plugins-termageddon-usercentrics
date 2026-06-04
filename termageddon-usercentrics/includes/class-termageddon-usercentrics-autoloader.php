<?php
/**
 * Autoloader for the plugin's own classes.
 *
 * Historically every class was pulled in by hand with `require_once` inside
 * load_dependencies(). That works for the happy path, but it makes a single missed
 * require — or a partial/stale deploy that ships an outdated loader alongside newer
 * files — fatal the entire site with "Class ... not found" (see the 1.10.0
 * Termageddon_Usercentrics_Geo_Api admin-ajax regression).
 *
 * This autoloader is the safety net: as long as a `Termageddon_Usercentrics*` class
 * file is present on disk it will resolve on demand, regardless of which require_once
 * calls did or did not run. It only ever handles this plugin's prefix, so it never
 * interferes with Composer's vendor autoloader or WordPress core.
 *
 * @link       https://termageddon.com
 * @since      1.10.1
 *
 * @package    Termageddon_Usercentrics
 * @subpackage Termageddon_Usercentrics/includes
 */

/**
 * Plugin class autoloader.
 *
 * @since      1.10.1
 * @package    Termageddon_Usercentrics
 * @subpackage Termageddon_Usercentrics/includes
 * @author     Termageddon <support@termageddon.com>
 */
class Termageddon_Usercentrics_Autoloader {

	/**
	 * Only classes beginning with this prefix are owned by the plugin.
	 *
	 * @var string
	 */
	const PREFIX = 'Termageddon_Usercentrics';

	/**
	 * Directories (relative to the plugin root) searched for class files.
	 *
	 * @var array<int,string>
	 */
	const CLASS_DIRS = array( 'includes/', 'admin/', 'public/' );

	/**
	 * Register the autoloader with the SPL stack. Safe to call more than once.
	 *
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Resolve a class name to the absolute path of its file, or null if the class
	 * is not owned by the plugin or no matching file exists.
	 *
	 * Follows the WordPress file-naming convention: `Foo_Bar_Baz` maps to
	 * `class-foo-bar-baz.php`.
	 *
	 * @param string $class_name Fully qualified class name being loaded.
	 * @return string|null Absolute path to the class file, or null.
	 */
	public static function resolve( $class_name ) {
		if ( 0 !== strpos( $class_name, self::PREFIX ) ) {
			return null;
		}

		if ( ! defined( 'TERMAGEDDON_COOKIE_PATH' ) ) {
			return null;
		}

		$file = 'class-' . str_replace( '_', '-', strtolower( $class_name ) ) . '.php';

		foreach ( self::CLASS_DIRS as $dir ) {
			$path = TERMAGEDDON_COOKIE_PATH . $dir . $file;
			if ( is_readable( $path ) ) {
				return $path;
			}
		}

		return null;
	}

	/**
	 * SPL autoload callback. Requires the class file when one can be resolved.
	 *
	 * @param string $class_name Fully qualified class name being loaded.
	 * @return void
	 */
	public static function autoload( $class_name ) {
		$path = self::resolve( $class_name );
		if ( null !== $path ) {
			require_once $path;
		}
	}
}
