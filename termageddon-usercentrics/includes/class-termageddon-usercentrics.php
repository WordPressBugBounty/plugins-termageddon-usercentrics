<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://termageddon.com
 * @since      1.0.0
 *
 * @package    Termageddon_Usercentrics
 * @subpackage Termageddon_Usercentrics/includes
 */

use GeoIp2\Database\Reader;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Termageddon_Usercentrics
 * @subpackage Termageddon_Usercentrics/includes
 * @author     Termageddon <support@termageddon.com>
 */
class Termageddon_Usercentrics {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Termageddon_Usercentrics_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'TERMAGEDDON_COOKIE_VERSION' ) ) {
			$this->version = TERMAGEDDON_COOKIE_VERSION;
		}
		$this->plugin_name = 'termageddon-usercentrics';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_extra_hooks();
	}

	/**
	 * The wp_kses() allowed html for any echoed code.
	 *
	 * @since    1.1.1
	 * @access   private
	 * @var      string    $allowed_html    The array of allowed html tags passed into the wp_kses function.
	 */
	public const ALLOWED_HTML = array(
		'link'   => array(
			'rel'   => array(),
			'href'  => array(),
			'as'    => array(),
			'value' => array(),
		),
		'script' => array(
			'type'              => array(),
			'id'                => array(),
			'src'               => array(),
			'data-settings-id'  => array(),
			'data-usercentrics' => array(),
			'data-version'      => array(),
			'async'             => array(),
		),
		'style'  => array(
			'id' => array(),
		),
	);

	/**
	 *  Returns a key value pair of geolocations to iterate over.
	 *  To add support for a new state, add a new key to the array and add the state to the $GEOLOCATION_KEY_TO_STATE array.
	 *
	 *  @param boolean $include_sections Whether or not to include the section keys. Defaults to false.
	 *  @return array
	 */
	public static function get_geolocation_locations( $include_sections = false ): array {
		$list = array(
			'eu'          => array(
				'title'   => __( 'European Union & European Economic Area (GDPR)', 'termageddon-usercentrics' ),
				'popular' => true,
			),
			'uk'          => array(
				'title'   => __( 'United Kingdom (UK DPA)', 'termageddon-usercentrics' ),
				'popular' => true,
			),
			'canada'      => array(
				'title'   => __( 'Canada (PIPEDA, Quebec 25)', 'termageddon-usercentrics' ),
				'popular' => true,
			),
			'section_usa' => array(
				'title'   => __( 'United States of America', 'termageddon-usercentrics' ),
				'popular' => false,
			),
			'california'  => array(
				'title'   => __( 'California (CPRA, CIPA)', 'termageddon-usercentrics' ),
				'popular' => true,
			),
			'colorado'    => array(
				'title'   => __( 'Colorado (CPA)', 'termageddon-usercentrics' ),
				'popular' => false,
			),
			'connecticut' => array(
				'title'   => __( 'Connecticut (CTDPA)', 'termageddon-usercentrics' ),
				'popular' => false,
			),
			'oregon'      => array(
				'title'   => __( 'Oregon (OCPA)', 'termageddon-usercentrics' ),
				'popular' => false,
			),
			'texas'       => array(
				'title'   => __( 'Texas (TDPSA)', 'termageddon-usercentrics' ),
				'popular' => false,
			),
			'utah'        => array(
				'title'   => __( 'Utah (UCPA)', 'termageddon-usercentrics' ),
				'popular' => false,
			),
			'virginia'    => array(
				'title'   => __( 'Virginia (VCDPA)', 'termageddon-usercentrics' ),
				'popular' => false,
			),
		);

		if ( $include_sections ) {
			return $list;
		}
		return array_filter(
			$list,
			function( $key ) {
				return strpos( $key, 'section_' ) !== 0;
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 *  Maps the geolocation key to the state returned by the geolocation lookup.
	 *
	 *  @return array of key value matchups
	 */
	public const GEOLOCATION_KEY_TO_STATE = array(
		'california'  => 'California',
		'colorado'    => 'Colorado',
		'connecticut' => 'Connecticut',
		'oregon'      => 'Oregon',
		'texas'       => 'Texas',
		'utah'        => 'Utah',
		'virginia'    => 'Virginia',
	);

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Termageddon_Usercentrics_Loader. Orchestrates the hooks of the plugin.
	 * - Termageddon_Usercentrics_I18n. Defines internationalization functionality.
	 * - Termageddon_Usercentrics_Admin. Defines all hooks for the admin area.
	 * - Termageddon_Usercentrics_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		* The external-dependencies allowing additional functionality such as GEOIP
		*/
		require_once TERMAGEDDON_COOKIE_PATH . 'vendor/autoload.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once TERMAGEDDON_COOKIE_PATH . 'includes/class-termageddon-usercentrics-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once TERMAGEDDON_COOKIE_PATH . 'includes/class-termageddon-usercentrics-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once TERMAGEDDON_COOKIE_PATH . 'admin/class-termageddon-usercentrics-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once TERMAGEDDON_COOKIE_PATH . 'public/class-termageddon-usercentrics-public.php';

		$this->loader = new Termageddon_Usercentrics_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Termageddon_Usercentrics_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Termageddon_Usercentrics_I18n();

		$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the extrahooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_extra_hooks() {
		// Register the custom schedule.
		$this->loader->add_filter( 'cron_schedules', $this, 'register_schedules', 10, 1 );

		// Register the possibility of query debug filter.
		$this->loader->add_filter( 'query_vars', $this, 'add_query_debug_filter' );

		// Add in plugin settings link to plugin list page.
		$this->loader->add_filter( 'plugin_action_links_' . TERMAGEDDON_COOKIE_EXEC_RELATIVE_PATH, $this, 'register_plugin_settings_link' );

		// Register action to verify the database to allow the cron jobs to work.
		$this->loader->add_action( 'termageddon_usercentrics_maxmind_download', $this, 'verify_maxmind_database' );

		// Add PSL shortcode.
		add_shortcode(
			'uc-privacysettings',
			function( $atts ) {
				$a = shortcode_atts(
					array(
						'text' => 'Privacy Settings',
					),
					$atts
				);

				return '<a href="javascript:void(0)" onclick="(function(){var r=document.querySelector(\'div#usercentrics-root\'),c=document.querySelector(\'aside#usercentrics-cmp-ui\');if(r)r.style.display=\'block\';if(c)c.style.display=\'block\';if(typeof UC_UI!==\'undefined\')UC_UI.showSecondLayer()})()" id="usercentrics-psl">' . $a['text'] . '</a>';
			}
		);

	}

	/**
	 * Register the custom time schedule
	 *
	 * @param mixed $schedules the existing schedules to alter.
	 * @return mixed
	 */
	public function register_schedules( $schedules ) {
		$schedules['termageddon_usercentrics_every_month'] = array(
			'interval' => MONTH_IN_SECONDS,
			'display'  => __( 'Every Month', 'termageddon-usercentrics' ),
		);

		return $schedules;
	}


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Termageddon_Usercentrics_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		// $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Register Menu page.
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_page_config' );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_all_settings' );

		// If AJAX Mode is enabled, load geolocation ajax actions.
		$this->loader->add_action( 'wp_ajax_uc_geolocation_lookup', $this, 'geolocation_lookup_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_uc_geolocation_lookup', $this, 'geolocation_lookup_ajax' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Termageddon_Usercentrics_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		if ( self::is_geoip_enabled() && ! self::is_ajax_mode_enabled() && ! wp_doing_cron() ) {
			$this->loader->add_action( 'init', $this, 'lookup_ip_address' );
		}
		// If AJAX Mode is enabled, load geolocation ajax actions.
		$this->loader->add_action( 'wp_ajax_uc_geolocation_lookup', $this, 'geolocation_lookup_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_uc_geolocation_lookup', $this, 'geolocation_lookup_ajax' );

		// WP Head Implementation.
		$this->loader->add_action(
			'wp_head',
			$plugin_public,
			'build_termageddon_script',
			self::get_embed_priority()
		);

		// Load the primary embed (or disabled) script in the head.
		$this->loader->add_action(
			'wp_enqueue_scripts',
			$plugin_public,
			'build_termageddon_enqueue',
			self::get_embed_priority()
		);

		$this->loader->add_filter(
			'script_loader_tag',
			$plugin_public,
			'filter_script_loader_tag',
			10,
			3
		);

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Termageddon_Usercentrics_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}


	/**
	 * This checks and returns the execution time in seconds of the callable function.
	 *
	 * @param callable $function The callback function to check the execution time of.
	 * @return int|float $seconds - The amount of time that has passed
	 */
	public static function check_execution_time( callable $function ) {
		// Starting clock time in seconds.
		$start_time = microtime( true );

		call_user_func( $function );

		// End clock time in seconds.
		$end_time = microtime( true );

		// Calculate script execution time.
		$execution_time = ( $end_time - $start_time );

		return $execution_time;
	}


	/**
	 * Generate a random string with specified parameters
	 *
	 * @param int   $length The length of string to generate.
	 * @param array $options The various options to pass in. 'type' is a valid option.
	 * @return string $randomString - The randomized string
	 */
	public static function generate_random_string( int $length = 10, array $options = array() ) {
		$type = ( isset( $options['type'] ) ? $options['type'] : '' );
		switch ( strtolower( $type ) ) {
			case 'letters':
				$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
			case 'numbers':
				$characters = '0123456789';
				break;
			default:
				$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
		}

		// Generate String.
		$characters_length = strlen( $characters );
		$random_string     = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$random_string .= $characters[ wp_rand( 0, $characters_length - 1 ) ];
		}

		return $random_string;

	}



	/**
	 * Setup the debug variable to support the debug variable.
	 *
	 * @param mixed $vars the filters that already exist.
	 * @return mixed
	 */
	public function add_query_debug_filter( $vars ) {
		$vars[] = $this->plugin_name . '-debug';
		return $vars;
	}


	/**
	 * Add in the Settings link to the plugins.php list page.
	 *
	 * @param array $links the links already in the list.
	 * @return array
	 */
	public function register_plugin_settings_link( $links ) {
		// Build and escape the URL.
		$url = esc_url(
			add_query_arg(
				'page',
				$this->plugin_name,
				get_admin_url() . 'tools.php'
			)
		);
		// Create the link.
		$settings_link = "<a href='$url'>" . __( 'Settings', 'termageddon-usercentrics' ) . '</a>';
		// Adds the link to the end of the array.
		array_unshift(
			$links,
			$settings_link
		);
		return $links;
	}


	/**
	 * This will only execute if executed by a cron job, or the database does not exist.
	 *
	 * @return bool Returns true if database is downloaded. false if not.
	 */
	public static function verify_maxmind_database() {
		// Check for fatal errors.
		if ( self::check_for_download_errors() ) {
			return false;
		}

		// If Geo IP is enabled, download.
		if ( ! self::is_geoip_enabled() ) {
			return false;
		}

		$path = self::get_maxmind_db_path();

		if ( ! file_exists( $path ) || wp_doing_cron() ) {

			if ( ! is_dir( dirname( $path ) ) ) {
				@wp_mkdir_p( dirname( $path ) );
			}

			self::download_maxmind_db( $path );

		}

		return file_exists( $path );
	}


	/** Identify if three failed downloads have occurred.
	 *
	 * @return bool  */
	public static function check_for_download_errors(): bool {
		return ( self::count_download_errors() > 5 );
	}

	/**
	 * Return the integer count of database download errors.
	 *
	 * @return int  */
	public static function count_download_errors(): int {
		return (int) get_option( 'termageddon_usercentrics_download_error_count', 0 );
	}

	/**
	 * Check if migration is needed by looking for a settings ID in the embed code.
	 *
	 * @return bool True if migration is needed, false otherwise.
	 */
	public static function check_for_conversion_needed(): bool {
		$embed_code = self::get_embed_code();
		return (bool) ! empty( $embed_code ) && preg_match( '/data-settings-id="[^"]*"/', $embed_code );
	}


	/**
	 * Return a list of error logs generated by the download.
	 *
	 * @return array  */
	public static function get_download_error_logs(): array {
		return (array) array_filter( get_option( 'termageddon_usercentrics_download_error_log', array() ) );
	}

	/**
	 * Filter out the standard embed code when a settings ID is present.
	 *
	 * @param string $embed_code The embed code to filter.
	 * @return string The filtered embed code.
	 */
	public static function filter_out_standard_embed_code( string $embed_code ): string {
		if ( empty( self::get_settings_id() ) ) {
			return $embed_code;
		}

		// Check if $embed_code is not null before running preg_replace.
		if ( null !== $embed_code ) {
			$embed_code = preg_replace( '~<link rel="preconnect" href="\/\/privacy-proxy.usercentrics.eu">~', '', $embed_code );
			$embed_code = preg_replace( '~<link rel="preload" href="//privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js" as="script">~', '', $embed_code );
			$embed_code = preg_replace( '~<script type="application/javascript" src="https:\/\/privacy-proxy.usercentrics.eu\/latest\/uc-block.bundle.js"><\/script>~', '', $embed_code );
			$embed_code = preg_replace( '~<script id="usercentrics-cmp".*async>.*<\/script>~', '', $embed_code );
			$embed_code = preg_replace( '~<script>uc.setCustomTranslations\(\'https://termageddon.ams3.cdn.digitaloceanspaces.com/translations/\'\);</script>~', '', $embed_code );
		}

		return trim( $embed_code );
	}
	/**
	 * Append the settings ID embed code to the existing embed code.
	 *
	 * @param string $embed_code The embed code to append to.
	 * @return string The embed code with settings ID code appended.
	 */
	public static function append_settings_id_embed_code( string $embed_code ): string {
		if ( empty( self::get_settings_id() ) ) {
			return $embed_code;
		}

		$embed_version = self::get_embed_script_version();
		$loader_url    = ( 'v2' === $embed_version ) ? '//app.usercentrics.eu/browser-ui/latest/loader.js' : '//web.cmp.usercentrics.eu/ui/loader.js';

		$new_embed_code  = '<link rel="preconnect" href="//privacy-proxy.usercentrics.eu">' . PHP_EOL;
		$new_embed_code .= '<link rel="preload" href="//privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js" as="script">' . PHP_EOL;
		$new_embed_code .= '<script type="application/javascript" src="//privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js"></script>' . PHP_EOL;
		$new_embed_code .= '<script id="usercentrics-cmp" data-cmp-version="' . esc_attr( self::get_embed_script_version() ) . '" src="' . esc_url( $loader_url ) . '" data-settings-id="' . self::get_settings_id() . '"  async></script>' . PHP_EOL;
		$new_embed_code .= '<script>uc.setCustomTranslations(\'https://termageddon.ams3.cdn.digitaloceanspaces.com/translations/\');</script>' . PHP_EOL;
		$new_embed_code .= self::filter_out_standard_embed_code( $embed_code );

		return $new_embed_code;
	}

	/**
	 * Get the injection method for the embed code.
	 *
	 * @return string The injection method, defaults to 'wp_head'. Alternatively, 'wp_enqueue_scripts' is also supported.
	 */
	public static function get_embed_injection_method(): string {
		return get_option( 'termageddon_usercentrics_embed_injection_method', 'wp_head' );
	}


	/**
	 * Get the version of the embed script to use.
	 *
	 * @return string The embed script version, defaults to 'v2'.
	 */
	public static function get_embed_script_version(): string {
		return get_option( 'termageddon_usercentrics_embed_version', 'v2' );
	}

	/**
	 * Get the Usercentrics settings ID.
	 *
	 * @return string The settings ID, empty string if not set.
	 */
	public static function get_settings_id(): string {
		return get_option( 'termageddon_usercentrics_settings_id', '' );
	}

	/**
	 * Get the embed code.
	 * Valid Options:
	 *  'filter_standard_embed_code' => false, // Filter out the standard embed code to prevent duplication.
	 *  'force_include_embed_code' => false, // Force the inclusion of the embed code, even if the settings ID is not set due to the implementation mode.
	 *
	 * @param array $options The configuration options to build the embed code.
	 * @return string The embed code, empty string if not set.
	 */
	public static function get_embed_code( array $options = array() ): string {
		$embed_code = get_option( 'termageddon_usercentrics_embed_code', '' );
		// Options Configuration.
		$filter_standard_embed_code = ( isset( $options['filter_standard_embed_code'] ) ? $options['filter_standard_embed_code'] : false );
		$force_include_embed_code   = ( isset( $options['force_include_embed_code'] ) ? $options['force_include_embed_code'] : false );

		if ( $filter_standard_embed_code ) {
			$embed_code = self::filter_out_standard_embed_code( $embed_code );
		}

		if ( $force_include_embed_code ) {
			$embed_code = self::append_settings_id_embed_code( $embed_code );
		}

		return $embed_code;
	}

	/**
	 * Based on the error message getting passed in, log it, and iterate by one.
	 *
	 * @param string $error The string error message to save to the list.
	 * @return void
	 */
	private static function log_download_error( string $error ) {
		if ( defined( 'TERMAGEDDON_ERROR_HAS_BEEN_LOGGED' ) ) {
			return;
		}

		// Ensure that this only runs once per run.
		define( 'TERMAGEDDON_ERROR_HAS_BEEN_LOGGED', true );

		$error_count_option = 'termageddon_usercentrics_download_error_count';
		$error_log_option   = 'termageddon_usercentrics_download_error_log';

		// Iterate Count by one.
		$error_count = get_option( $error_count_option );
		if ( false !== $error_count ) {
			$error_count++;
			update_option( $error_count_option, $error_count );
		} else {
			add_option( $error_count_option, 1 );
		}

		// Append log to error.
		$error_logs = get_option( $error_log_option );
		if ( false !== $error_logs ) {
			$error_logs[] = gmdate( 'Y-m-d g:i:s T' ) . '	' . $error;
			update_option( $error_log_option, $error_logs );
		} else {
			add_option( $error_log_option, array( $error ) );
		}

		self::debug( 'TEMAGEDDON_CRITICAL_ERROR', $error, $error_count, $error_logs );

	}

	/**
	 * Download the latest version of the database to the folder.
	 *
	 * Source: Based on GeoTargeting Lite WordPress Plugin
	 * Plugin URI: https://wordpress.org/plugins/geotargeting/
	 * License: GNU 2
	 *
	 * @return bool
	 */
	private static function download_maxmind_db() {
		// If critical error, do not try to re-download this session.
		if ( defined( 'TERMAGEDDON_ERROR_HAS_BEEN_LOGGED' ) ) {
			return false;
		}

		// No errors, continue.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$path = self::get_maxmind_db_path();

		// Get Signed URL.
		try {
			$signed_url = self::get_maxmind_download_url();
		} catch ( \Throwable $th ) {
			self::log_download_error( $th->getMessage() );
			return false;
		}

		$database  = wp_basename( $path );
		$dest_dir  = trailingslashit( dirname( $path ) );
		$dest_path = $dest_dir . $database;

		self::debug( 'Downloading', $signed_url, $database, $dest_dir, is_writable( $dest_dir ), $dest_path );

		// Check writable nature of directory.
		if ( ! is_writable( $dest_dir ) ) {
			self::log_download_error( 'Download directory is not writable.' );
			return false;
		}

		$tmp_database_path = download_url( $signed_url );

		if ( ! is_wp_error( $tmp_database_path ) ) {
			try {
				// Remove old database to ensure it is up to date.
				if ( file_exists( $dest_path ) ) {
					unlink( $dest_path );
				}

				// Copy new database and delete tmp directories.
				rename( $tmp_database_path, $dest_path );

				// Ensure permissions are correct for downloaded file.
				chmod( $dest_path, 0644 );

				// Remove temp downloaded file.
				if ( file_exists( $tmp_database_path ) ) {
					unlink( $tmp_database_path );
				}

				return file_exists( $dest_path ) && is_readable( $dest_path );
			} catch ( Exception $e ) {
				self::log_download_error( 'Save Error: ' . $e->getMessage() );
			}
		} else {
			self::log_download_error( 'Download Error: ' . $tmp_database_path->get_error_message() );
		}
		return false;
	}


	/**
	 * We get user IP but check with different services to see if they provided real user ip
	 *
	 * Source: Based on GeoTargeting Lite WordPress Plugin
	 * Plugin URI: https://wordpress.org/plugins/geotargeting/
	 * License: GNU 2
	 *
	 * @return mixed|void
	 */
	private static function get_ip_address() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '1.1.1.1';
		// Cloudflare.
		$ip = isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) : $ip;
		// Reblaze.
		$ip = isset( $_SERVER['X-Real-IP'] ) ? sanitize_text_field( wp_unslash( $_SERVER['X-Real-IP'] ) ) : $ip;
		// Sucuri.
		$ip = isset( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] ) ) : $ip;
		// Ezoic.
		$ip = isset( $_SERVER['X-FORWARDED-FOR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['X-FORWARDED-FOR'] ) ) : $ip;
		// Akamai.
		$ip = isset( $_SERVER['True-Client-IP'] ) ? sanitize_text_field( wp_unslash( $_SERVER['True-Client-IP'] ) ) : $ip;
		// Clouways.
		$ip = isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) : $ip;
		// Varnish Trash ?
		$ip = str_replace( array( '::ffff:', ', 127.0.0.1' ), '', $ip );
		// Get varnish first ip.
		$ip = strstr( $ip, ',' ) === false ? $ip : strstr( $ip, ',', 1 );

		return apply_filters( 'process_user_ip', $ip );
	}


	/**
	 * Process the ip address to look for testing overrides and move forward.
	 *
	 * @return string $ip_address
	 */
	public static function get_processed_ip_address() {
		$ip_address = self::get_ip_address();

		// Localhost Test IP Address.
		// '::1' === $ip_address.
		switch ( strtolower( get_query_var( 'termageddon-usercentrics-debug' ) ) ) {
			case 'colorado':
				$ip_address = '198.255.11.211'; // Colorado.
				break;

			case 'california':
				$ip_address = '149.142.201.252'; // California.
				break;

			case 'canada':
				$ip_address = '24.51.224.0'; // Canada.
				break;

			case 'denmark':
				$ip_address = '2.111.255.255'; // Denmark.
				break;

			case 'england':
				$ip_address = '217.61.20.213'; // England.
				break;

			case 'wales':
				$ip_address = '89.241.3.226'; // Wales.
				break;

			case 'france':
				$ip_address = '194.177.63.255'; // France.
				break;
			case '':
			default:
				break;
		}

		return $ip_address;
	}

	/** Retrieve signed URL from application for downloading maxmind database from Termageddon Server.
	 *
	 * @return string Signed URL for downloading maxmind.
	 * @throws Exception If download error occurs, or disallowed, throws exception.
	 */
	public static function get_maxmind_download_url(): string {

		$domain   = wp_parse_url( get_site_url(), PHP_URL_HOST );
		$api_url  = "https://app.termageddon.com/requestGeoIpDownloadLink?source=wordpress_plugin&domain={$domain}";
		$response = wp_remote_get( $api_url );

		self::debug( $api_url, $response );

		// Check for failure to call.
		if ( is_wp_error( $response ) ) {
			throw new Exception( 'URL Lookup Error #1: ' . $response->get_error_message() );
		}

		// Check for invalid response.
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			throw new Exception( 'URL Lookup Error #2 (' . wp_remote_retrieve_response_code( $response ) . '): ' . wp_remote_retrieve_response_message( $response ) );
		}

		// Calculate Body and json array from body.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Check to ensure data is an array.
		if ( ! is_array( $data ) ) {
			throw new Exception( 'URL Lookup Error #3: Unable to process body; ' . $data );
		}

		// Extract information from data.
		list('success' => $success, 'error' => $error, 'url' => $url) = $data;

		// Check for failure.
		if ( ! $success ) {
			throw new Exception( 'URL Lookup Error #4: ' . $error );
		}

		// Check for empty URL.
		if ( empty( $url ) ) {
			throw new Exception( 'URL Lookup Error #5: URL Empty' . $error );
		}

		return (string) $url;
	}

	/**
	 * Returns the correct path to use for the maxmind database file.
	 *
	 * @return string  */
	public static function get_maxmind_db_path() {
		// Locate MMDB File.
		$database_name = 'GeoLite2-City.mmdb';

		// Default path (Inside Plugins Dir).

		$path_upload = wp_upload_dir();
		return $path_upload['basedir'] . '/termageddon-maxmind/' . $database_name;

	}

	/**
	 * Returns the string last updated date.
	 *
	 * @return string  */
	public static function get_maxmind_db_last_updated(): string {
		if ( ! file_exists( self::get_maxmind_db_path() ) ) {
			return '-';
		}
		return get_date_from_gmt( gmdate( 'Y-m-d H:i:s', filemtime( self::get_maxmind_db_path() ) ), 'F j, Y g:i:s A' );

	}

	/**
	 * Returns the string last updated date.
	 *
	 * @return string  */
	public static function get_maxmind_db_next_update(): string {
		$date = wp_next_scheduled( 'termageddon_usercentrics_maxmind_download' );
		if ( false === $date ) {
			return '-';
		}

		return get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $date ), 'F j, Y g:i:s A' );

	}


	/**
	 * Returns whether debug mode is enabled via the query parameter
	 *
	 * @return bool
	 */
	public static function is_debug_mode_enabled() {
		return ( get_option( 'termageddon_usercentrics_location_debug', false ) ? true : false );

	}


	/**
	 * Returns whether disabled for troubleshooting mode is enabled and not in the query params
	 *
	 * @return bool
	 */
	public static function is_disabled_for_troubleshooting() {
		return ( get_option( 'termageddon_usercentrics_disable_troubleshooting', false ) ? true : false );

	}


	/**
	 * Returns whether user wants to force enable via the query params.
	 *
	 * @return bool
	 */
	public static function is_enabled_via_get_override() {
		return isset( $_GET['enable-usercentrics'] );

	}


	/**
	 * Returns whether debug mode is enabled via the query parameter
	 *
	 * @return bool
	 */
	public static function should_hide_psl() {
		return ( get_option( 'termageddon_usercentrics_location_psl_hide', false ) ? true : false );

	}


	/**
	 * Returns whether debug mode is enabled via the query parameter
	 *
	 * @return bool
	 */
	public static function should_use_alternate_psl() {
		return ( get_option( 'termageddon_usercentrics_psl_alternate', false ) ? true : false );

	}

	/**
	 * Quick debug message to administrators.
	 *
	 * @param mixed ...$msg The message or messages to display in the debug alert.
	 * @return void
	 */
	public static function debug( ...$msg ) {
		if ( ! self::is_debug_mode_enabled() ) {
			return; // Check to ensure debug mode is enabled.
		}

		if ( wp_doing_ajax() ) {
			return; // Check for Ajax.
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return; // Check for CLI.
		}

		// Display message on frontend.
		// echo '<div class="error"><pre>' . wp_json_encode( $msg, JSON_PRETTY_PRINT ) . '</pre></div>';.

		// Display message in browser console.
		echo '<script>
			console.log(\'TERMAGEDDON USERCENTRICS\', `' . wp_json_encode( $msg, JSON_PRETTY_PRINT ) . '`);
		</script>';
	}

	/**
	 * Lookup IP Address and returns an object with various information included.
	 *
	 * @param string $ip_address  the string IP address to lookup.
	 * @return array $returns 'city', 'state', 'country
	 */
	public static function lookup_ip_address( string $ip_address = '' ) {

		// By default, look at the current visitor's IP address.
		if ( empty( $ip_address ) ) {
			$ip_address = self::get_processed_ip_address();
		}

		$city    = null;
		$state   = null;
		$country = null;

		$cookie_title = self::get_cookie_title();

		// If Geo IP is enabled, download.
		if ( self::is_geoip_enabled() ) {
			// Validate Database && download database if needed.
			self::verify_maxmind_database();

			// If Email is not in blacklist, try to calculate geo ip location.
			if ( '::1' !== $ip_address ) {
				// Check for cached location via cookie, or check the geo ip database if no cookie found.
				if ( isset( $_COOKIE[ $cookie_title ] ) && ! self::is_debug_mode_enabled() ) {
					@list('city' => $city, 'state' => $state, 'country' => $country) = json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_title ] ) ), true );
				} else {
					try {

						$reader = new Reader( self::get_maxmind_db_path() );

						$record = $reader->City( $ip_address );

						if ( isset( $record->city->names ) && isset( $record->city->names['en'] ) ) {
							$city = $record->city->names['en'];
						}
						if ( isset( $record->subdivisions[0] ) && isset( $record->subdivisions[0]->names ) && isset( $record->subdivisions[0]->names['en'] ) ) {
							$state = $record->subdivisions[0]->names['en'];
						}
						if ( isset( $record->country->names ) && isset( $record->country->names['en'] ) ) {
							$country = $record->country->names['en'];
						}

						// If able to, set cookie to allow future page loads to simply use the cookie for processing.
						if ( ! headers_sent() && ! isset( $_COOKIE[ $cookie_title ] ) ) {
							$cookie_value = wp_json_encode(
								array(
									'city'    => $city,
									'state'   => $state,
									'country' => $country,
								)
							);

							setcookie(
								$cookie_title,
								$cookie_value,
								0,
								COOKIEPATH,
								COOKIE_DOMAIN
							);
							$_COOKIE[ $cookie_title ] = $cookie_value;
						}
					} catch ( \Throwable $th ) {
						// Error with GEO IP.
						// Display it IF debug via GET is enabled and administrator.
						if ( current_user_can( 'administrator' ) || self::is_debug_mode_enabled() ) {
							self::debug( 'Error Calculating Location', $th->getMessage() );
						}
					}
				}
			}
		}

		// Return the final value of the city, state, and country.
		return array(
			'city'    => $city,
			'state'   => $state,
			'country' => $country,
		);

	}


	/**
	 * Returns the current cookie title for us with geoip services.
	 *
	 * @return string  */
	public static function get_cookie_title() {
		return 'tu-geoip' . ( wp_doing_ajax() ? '-ajax' : '' );
	}


	/**
	 * Returns the human readable location of the current location
	 *
	 * @return string  */
	public static function get_location_displayname(): string {
		list('city' => $city, 'state' => $state, 'country' => $country) = self::lookup_ip_address();

		if ( empty( $city ) && empty( $state ) && empty( $country ) ) {
			return 'Unknown';
		}

		return trim( ( ! empty( $city ) ? $city . ', ' : '' ) . ( ! empty( $state ) ? $state . ' ' : '' ) . ( ! empty( $country ) ? $country . '' : '' ) );
	}


	/**
	 * Returns a human readable version of the allowed html tags.
	 *
	 * @return string
	 */
	public static function get_allowed_html_kses(): string {
		$allowed = wp_kses_allowed_html( self::ALLOWED_HTML );
		return wp_json_encode( $allowed, JSON_PRETTY_PRINT );
	}

	/**
	 * Returns the script priority from 1-10.
	 *
	 * @return int
	 */
	public static function get_embed_priority(): int {
		$priority = get_option( 'termageddon_usercentrics_embed_priority', 1 );
		$priority = intval( $priority );
		if ( $priority <= 10 && $priority >= 1 ) {
			return $priority;
		}
		return 1;
	}


	/** Identifies if any geoip location is enabled, despite if the locations are enabled.
	 *
	 * @return bool
	 */
	public static function is_geoip_location_enabled(): bool {
		$enabled = false;

		foreach ( self::get_geolocation_locations() as $loc_key => $loc ) {
			if ( self::is_geoip_location_enabled_in( $loc_key ) ) {
				$enabled = true;
				break;
			}
		}
		return $enabled;
	}

	/**
	 * Identifies if geoip location is required for a specific location.
	 *
	 * @param string $loc_key The location key to check.
	 * @return bool
	 */
	public static function is_geoip_location_enabled_in( string $loc_key ): bool {
		return get_option( 'termageddon_usercentrics_show_in_' . $loc_key, false ) ? true : false;
	}

	/** Identifies if user has enabled geoip location toggle.
	 *
	 * @return bool
	 */
	public static function is_geoip_enabled(): bool {
		$enabled = get_option( 'termageddon_usercentrics_geoip_enabled', 'not-exists' );
		if ( 'not-exists' === $enabled ) {
			$enabled = self::is_geoip_location_enabled();

			update_option( 'termageddon_usercentrics_geoip_enabled', $enabled ? '1' : '' );// Update value based on currently existing implementation.
			return $enabled;
		} else { // Otherwise, return new option value.
			return '1' === $enabled;
		}
	}


	/**
	 * Returns a list of all possible integrations
	 *
	 * @return array with the integration slugs
	 */
	public static function get_integrations(): array {
		return array(
			'divi_video'      => array(
				'name'        => __( 'Divi Video', 'termageddon-usercentrics' ),
				'description' => __( 'This resolves and improves the cookie-consent implementation when using an image placeholder overlay for the Divi video embed.', 'termageddon-usercentrics' ),
				'beta'        => false,
			),
			'elementor_video' => array(
				'name'        => __( 'Elementor Video', 'termageddon-usercentrics' ),
				'description' => __( 'This resolves and improves the cookie-consent implementation when using an image placeholder overlay for the Elementor video embed.', 'termageddon-usercentrics' ),
				'beta'        => false,
			),
			'powerpack_video' => array(
				'name'        => __( 'PowerPack Video', 'termageddon-usercentrics' ),
				'description' => __( 'This resolves and improves the cookie-consent implementation when using an image placeholder overlay for the PowerPack for BeaverBuilder video embed. This requires window events to be enabled in your Usercentrics settings. Please reach out to support if needed.', 'termageddon-usercentrics' ),
				'beta'        => true,
			),
			'presto_player'   => array(
				'name'        => __( 'Presto Player', 'termageddon-usercentrics' ),
				'description' => __( 'This resolves and improves the cookie-consent implementation when using an image placeholder overlay for the Presto Player video embed.', 'termageddon-usercentrics' ),
				'beta'        => false,
			),
			'uabb_video'      => array(
				'name'        => __( 'Ultimate Addons for Beaver Builder Video', 'termageddon-usercentrics' ),
				'description' => __( 'This resolves and improves the cookie-consent implementation when using an image placeholder overlay for the Ultimate Addons for Beaver Builder video embed.', 'termageddon-usercentrics' ),
				'beta'        => false,
			),
		);
	}


	/**
	 * Check if the given integration is enabled.
	 *
	 * @param string $integration The slug of the integration to check.
	 * @return bool
	 */
	public static function is_integration_enabled( string $integration ): bool {
		return get_option( 'termageddon_usercentrics_integration_' . $integration, false ) ? true : false;
	}

	/**
	 * Helper method to identify if the user is located in Colorado.
	 *
	 * @param string $loc_key The location key to check.
	 * @return bool
	 * @throws Exception If unable to locate location key.
	 */
	public static function is_located_in( string $loc_key ): bool {
		$function_name = 'is_located_in_' . $loc_key;
		if ( is_callable( array( self::class, $function_name ) ) ) {
			return call_user_func( array( self::class, $function_name ) );
		}
		// Default to state. Check if state mapping exists.
		if ( ! array_key_exists( $loc_key, self::GEOLOCATION_KEY_TO_STATE ) ) {
			throw new Exception( 'Unable to locate location key for ' . $loc_key );
		}

		$loc_key                  = self::GEOLOCATION_KEY_TO_STATE[ $loc_key ];
		list( 'state' => $state ) = self::lookup_ip_address();
		return ( null === $state || $loc_key === $state );

	}

	/**
	 * Helper method to identify if the user is located in Canada.
	 *
	 * @return bool  */
	public static function is_located_in_canada(): bool {
		list( 'country' => $country ) = self::lookup_ip_address();
		return ( null === $country || 'Canada' === $country );

	}

	/**
	 * Helper method to identify if the user is located in EU.
	 *
	 * @return bool  */
	public static function is_located_in_eu(): bool {
		list( 'country' => $country ) = self::lookup_ip_address();

		$country_list = array(
			'Austria',
			'Belgium',
			'Bulgaria',
			'Croatia',
			'Cyprus',
			'Czech Republic',
			'Denmark',
			'Estonia',
			'Finland',
			'France',
			'Germany',
			'Greece',
			'Hungary',
			'Ireland',
			'Italy',
			'Latvia',
			'Lithuania',
			'Luxembourg',
			'Malta',
			'Netherlands',
			'Poland',
			'Portugal',
			'Romania',
			'Slovakia',
			'Slovenia',
			'Spain',
			'Sweden',
			// 'United Kingdom',
			'Norway',
			'Iceland',
			'Liechtenstein',
		);
		return ( null === $country || in_array( $country, $country_list, true ) );

	}

	/**
	 * Helper method to identify if the user is located in UK.
	 *
	 * @return bool  */
	public static function is_located_in_uk(): bool {
		list( 'country' => $country ) = self::lookup_ip_address();
		return ( null === $country || 'United Kingdom' === $country );

	}


	/**
	 * Check the geolocation settings, and decide if the widget should be hidden.
	 *
	 * @return bool  */
	public static function should_hide_due_to_location(): bool {

		// Iterate through locations and identify if user is located in any of them, and site has it enabled. If so, hide consent.
		$located_in_location_that_needs_consent = false;
		foreach ( self::get_geolocation_locations() as $loc_key => $loc ) {
			$is_located_in = self::is_located_in( $loc_key );
			if ( $is_located_in ) {
				$located_in_location_that_needs_consent = true;
			}
			if ( $is_located_in && ! self::is_geoip_location_enabled_in( $loc_key ) ) {
				return true; // User is located in a location that needs it, but it is disabled, so hide.
			}
		}

		// If not in any applicable zones, hide cookie consent.
		if ( ! $located_in_location_that_needs_consent ) {
			return true; // Not in a location that needs it, so continue.
		}

		return false;
	}


	// ================================= //
	// ======== AJAX MODE LOGIC ======== //
	// ================================= //


	/**
	 * Verifies if ajax mode is enabled to check user location via AJAX instead of on page load.
	 *
	 * Returns false if geoip is not enabled or ajax mode is not enabled.
	 *
	 * @return bool  */
	public static function is_ajax_mode_enabled(): bool {
		if ( ! self::is_geoip_enabled() ) {
			return false;
		}

		return get_option( 'termageddon_usercentrics_location_ajax', true ) ? true : false;

	}

	/**
	 * Build ajax data response.
	 *
	 * @return array  */
	public static function build_ajax_response() {
		// Output debug message to console.
		$result = array(
			'hide' => self::should_hide_due_to_location(),
		);

		if ( self::is_debug_mode_enabled() ) {
			$ip_address = self::get_processed_ip_address();

			// Lookup IP Address or pull from Cookie.
			list('city' => $city, 'state' => $state, 'country' => $country) = self::lookup_ip_address( $ip_address );

			$result['ipAddress'] = $ip_address;
			$result['city']      = ( $city ?? 'Unknown' );
			$result['state']     = ( $state ?? 'Unknown' );
			$result['country']   = ( $country ?? 'Unknown' );

			// Iterate through locations.
			$locations = array();
			foreach ( self::get_geolocation_locations() as $loc_key => $loc ) {
				$locations[ $loc_key ] = self::is_located_in( $loc_key );
			}
			$result['locations'] = $locations;
		}

		return $result;
	}

	/**
	 * The admin-ajax hook to handle lookups via ajax via AJAX to bypass cache.
	 * Expects `nonce` being passed in
	 *
	 * @return void  */
	public function geolocation_lookup_ajax() {
		header( 'Content-Type: application/json; charset=utf-8' );

		$result = function( bool $success, string $message = '', array $data = null ) {
			$result_array = array(
				'success' => $success,
			);

			if ( '' !== $message ) {
				$result_array['message'] = $message;
			}

			if ( null !== $data ) {
				$result_array['data'] = $data;
			}

			echo wp_json_encode( $result_array );
			wp_die();
		};

		// If nonce is not provided, or is invalid.
		if ( ! isset( $_REQUEST['nonce'] ) ) {
			$result( false, 'Invalid Request' );
		}
		if ( isset( $_REQUEST['nonce'] ) && ! wp_verify_nonce( sanitize_key( $_REQUEST['nonce'] ), $this->plugin_name . '_ajax_nonce' ) ) {
			$result( false, 'Unauthorized' );
		}

		// Check for Location Override.
		if ( isset( $_REQUEST['location'] ) ) {
			set_query_var( 'termageddon-usercentrics-debug', sanitize_text_field( wp_unslash( $_REQUEST['location'] ) ) );
		}

		$result( true, '', self::build_ajax_response() );

		$result( false, 'Unknown error has occurred' );

	}

	/**
	 * Get the providers that should have blocking disabled
	 *
	 * @return array Array of provider IDs that should have blocking disabled
	 */
	public static function get_disabled_blocking_providers(): array {
		return get_option( 'termageddon_usercentrics_disable_blocking_providers', array() );
	}

	/**
	 * Get the providers that should trigger a page reload on opt-in
	 *
	 * @return array Array of provider IDs that should trigger a page reload on opt-in
	 */
	public static function get_auto_refresh_providers(): array {
		return get_option( 'termageddon_usercentrics_auto_refresh_providers', array() );
	}

}
