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
		Termageddon_Usercentrics_Legacy_Geolocation_Cleanup::maybe_run();
		$this->setup_translations();
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
			'data-uc-untouch'   => array(),
			'data-version'      => array(),
			'async'             => array(),
		),
		'style'  => array(
			'id' => array(),
		),
	);

	/**
	 *  Returns a key value pair of geolocations to iterate over.
	 *  To add support for a new state, add a new key here and a region-code rule in the hosted geolocation helper.
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
			'delaware'    => array(
				'title'   => __( 'Delaware (DPDPA)', 'termageddon-usercentrics' ),
				'popular' => false,
			),
			'florida'     => array(
				'title'   => __( 'Florida', 'termageddon-usercentrics' ),
				'popular' => false,
			),
			'indiana'     => array(
				'title'   => __( 'Indiana (ICDPA)', 'termageddon-usercentrics' ),
				'popular' => false,
			),
			'montana'     => array(
				'title'   => __( 'Montana (MCDPA)', 'termageddon-usercentrics' ),
				'popular' => false,
			),
			'new_jersey'  => array(
				'title'   => __( 'New Jersey (NJDPA)', 'termageddon-usercentrics' ),
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
		 * Composer-managed dependencies.
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

		/**
		 * The class encapsulating the hosted geolocation API.
		 */
		require_once TERMAGEDDON_COOKIE_PATH . 'includes/class-termageddon-usercentrics-geo-api.php';

		/**
		 * One-time cleanup for files and state left by the removed legacy service.
		 */
		require_once TERMAGEDDON_COOKIE_PATH . 'includes/class-termageddon-usercentrics-legacy-geolocation-cleanup.php';

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
	public static function setup_translations() {

		Termageddon_Usercentrics_I18n::load_plugin_textdomain();

	}

	/**
	 * Register all of the extrahooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_extra_hooks() {
		// Register the possibility of query debug filter.
		$this->loader->add_filter( 'query_vars', $this, 'add_query_debug_filter' );

		// Add in plugin settings link to plugin list page.
		$this->loader->add_filter( 'plugin_action_links_' . TERMAGEDDON_COOKIE_EXEC_RELATIVE_PATH, $this, 'register_plugin_settings_link' );

		// Add PSL shortcode.
		add_shortcode( 'uc-privacysettings', array( $this, 'privacy_settings_shortcode' ) );

	}

	/**
	 * Handle the uc-privacysettings shortcode
	 *
	 * @since    1.8.2
	 * @param    array $atts Shortcode attributes.
	 * @return   string The shortcode output.
	 */
	public function privacy_settings_shortcode( $atts ) {
		$a = shortcode_atts(
			array(
				'text' => 'Privacy Settings',
				'type' => 'a',
			),
			$atts
		);

		$onclick = '(function(){var r=document.querySelector(\'div#usercentrics-root\'),c=document.querySelector(\'aside#usercentrics-cmp-ui\');if(r)r.style.display=\'block\';if(c)c.style.display=\'block\';if(typeof UC_UI!==\'undefined\')UC_UI.showSecondLayer()})()';

		if ( 'button' === $a['type'] ) {
			return '<button type="button" onclick="' . $onclick . '" id="usercentrics-psl">' . esc_html( $a['text'] ) . '</button>';
		} else {
			return '<a role="button" href="javascript:void(0)" onclick="' . $onclick . '" id="usercentrics-psl">' . esc_html( $a['text'] ) . '</a>';
		}
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

		$this->loader->add_filter(
			'wc_facebook_pixel_script_attributes',
			$plugin_public,
			'filter_meta_for_woocommerce_pixel_script_attributes'
		);

		$this->loader->add_filter(
			'facebook_signals_held',
			$plugin_public,
			'hold_meta_for_woocommerce_signals'
		);

		$this->loader->add_action(
			'wp_footer',
			$plugin_public,
			'release_meta_for_woocommerce_signals',
			99
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
	 * Check if migration is needed by looking for a settings ID in the embed code.
	 *
	 * @return bool True if migration is needed, false otherwise.
	 */
	public static function check_for_conversion_needed(): bool {
		$embed_code = self::get_embed_code();
		return (bool) ! empty( $embed_code ) && preg_match( '/data-settings-id="[^"]*"/', $embed_code );
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
			$embed_code = preg_replace( '~<script>uc.setCustomTranslations\(.*\);</script>~', '', $embed_code );
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

		$embed_version        = self::get_embed_script_version();
		$loader_url           = ( 'v2' === $embed_version ) ? '//app.usercentrics.eu/browser-ui/latest/loader.js' : '//web.cmp.usercentrics.eu/ui/loader.js';
		$translations_url     = self::get_translations_url();
		$use_manual_control   = self::is_auto_blocker_disabled();
		$remove_preconnect    = self::are_preconnect_links_disabled();

		$new_embed_code = '';

		if ( ! $remove_preconnect ) {
			$new_embed_code .= '<link rel="preconnect" href="//privacy-proxy.usercentrics.eu">' . PHP_EOL;
		}
		
		// Only include the auto-blocking script if manual control is disabled.
		if ( ! $use_manual_control ) {
			if ( ! $remove_preconnect ) {
				$new_embed_code .= '<link rel="preload" href="//privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js" as="script">' . PHP_EOL;
			}
			$new_embed_code .= '<script type="application/javascript" src="//privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js" data-no-optimize="1" data-no-defer="1"></script>' . PHP_EOL;
			$new_embed_code .= '<script data-no-optimize="1" data-no-defer="1">uc.setCustomTranslations(\'' . $translations_url . '\');</script>' . PHP_EOL;
		}
		
		$new_embed_code .= '<script id="usercentrics-cmp" data-cmp-version="' . esc_attr( self::get_embed_script_version() ) . '" src="' . esc_url( $loader_url ) . '" data-settings-id="' . self::get_settings_id() . '" data-no-optimize="1" data-no-defer="1" async></script>' . PHP_EOL;
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
	 * Check whether the Usercentrics preconnect and preload links are disabled.
	 *
	 * @return bool True when the links should be omitted.
	 */
	public static function are_preconnect_links_disabled(): bool {
		return (bool) get_option( 'termageddon_usercentrics_disable_preconnect_links', false );
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
	 * Returns whether debug mode is enabled via the query parameter
	 *
	 * @return bool
	 */
	public static function is_debug_mode_enabled() {
		return ( get_option( 'termageddon_usercentrics_location_debug', false ) ? true : false );

	}

	/**
	 * Returns whether Disable CDN for Translations Script is set to Yes in the query params
	 *
	 * @return bool
	 */
	public static function is_cdn_disabled() {
		return ( get_option( 'termageddon_usercentrics_disable_cdn', false ) ? true : false );

	}

	/**
	 * Returns the translations URL for the Usercentrics script.
	 *
	 * @return string
	 */
	public static function get_translations_url() {
		$translations_url = 'https://termageddon.ams3.cdn.digitaloceanspaces.com/translations/';
		if ( self::is_cdn_disabled() ) {
			$translations_url = 'https://app.termageddon.com/js/uc/translations/';
		}
		return $translations_url;
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
	 * Returns a human readable version of the allowed html tags.
	 *
	 * @return string
	 */
	public static function get_allowed_html_kses(): string {
		$allowed = wp_kses_allowed_html( self::ALLOWED_HTML );
		return wp_json_encode( $allowed, JSON_PRETTY_PRINT );
	}

	/**
	 * Processes the embed code to ensure it is safe to use while preserving script contents.
	 *
	 * @param string $embed_code The embed code to process.
	 * @return string The processed embed code.
	 */
	public static function processEmbedCode( string $embed_code ): string {
		// Extract script tags and their contents
		$scripts = array();
		$pattern = '/<script[^>]*>(?!\s*<\/script>)([\s\S]*?)<\/script>/ms';

		// Replace scripts with placeholders so bypass wp_kses validation.
		$embed_code = preg_replace_callback($pattern, function($matches) use (&$scripts) {
			if (strpos($matches[0], 'termageddon.ams3.cdn.digitaloceanspaces.com') !== false) {
				return $matches[0];
			}
			$placeholder = '<!--SCRIPT_' . count($scripts) . '-->';
			$scripts[] = $matches[0];
			return $placeholder;
		},  $embed_code);

		// Filter remaining HTML with wp_kses.
		$filtered_code = wp_kses($embed_code, self::ALLOWED_HTML);

		// Restore script tags
		foreach ($scripts as $i => $script) {
			$filtered_code = str_replace("<!--SCRIPT_$i-->", $script, $filtered_code);
		}

		return $filtered_code;
	}

	/**
	 * Returns the script priority from 1-10.
	 *
	 * @return int
	 */
	public static function get_embed_priority(): int {
		$priority = get_option( 'termageddon_usercentrics_embed_priority', 1 );
		$priority = intval( $priority );
		if ( $priority <= 9999 && $priority >= -9999 ) {
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
				'name'             => __( 'Divi Video', 'termageddon-usercentrics' ),
				'description'      => __( 'This resolves and improves the cookie-consent implementation when using an image placeholder overlay for the Divi video embed.', 'termageddon-usercentrics' ),
				'beta'             => false,
				'default'          => false,
				'installed_themes' => array( 'Divi' ),
			),
			'elementor_video' => array(
				'name'              => __( 'Elementor Video', 'termageddon-usercentrics' ),
				'description'       => __( 'This resolves and improves the cookie-consent implementation when using an image placeholder overlay for the Elementor video embed.', 'termageddon-usercentrics' ),
				'beta'              => false,
				'default'           => false,
				'installed_plugins' => array( 'elementor/elementor.php' ),
			),
			'powerpack_video' => array(
				'name'              => __( 'PowerPack Video', 'termageddon-usercentrics' ),
				'description'       => __( 'This resolves and improves the cookie-consent implementation when using an image placeholder overlay for the PowerPack for BeaverBuilder video embed. This requires window events to be enabled in your Usercentrics settings. Please reach out to support if needed.', 'termageddon-usercentrics' ),
				'beta'              => false,
				'default'           => false,
				'installed_plugins' => array( 'bbpowerpack/bb-powerpack.php' ),
			),
			'presto_player'   => array(
				'name'              => __( 'Presto Player', 'termageddon-usercentrics' ),
				'description'       => __( 'This resolves and improves the cookie-consent implementation when using an image placeholder overlay for the Presto Player video embed.', 'termageddon-usercentrics' ),
				'beta'              => false,
				'default'           => false,
				'installed_plugins' => array( 'presto-player/presto-player.php' ),
			),
			'gravityforms_recaptcha' => array(
				'name'              => __( 'Gravity Forms - reCaptcha Extension', 'termageddon-usercentrics' ),
				'description'       => __( 'Enabling this feature will provide the Usercentrics blocker message, requiring users to consent first prior to loading in any Gravity Forms forms that have the embedded reCaptcha feature. After enabling, be sure to test in a fresh incognito window.', 'termageddon-usercentrics' ),
				'beta'              => true,
				'default'           => false,
				'installed_plugins' => array( 'gravityforms/gravityforms.php' ),
			),
			'meta_for_woocommerce' => array(
				'name'              => __( 'Meta for WooCommerce', 'termageddon-usercentrics' ),
				'description'       => __( 'Enabling this feature helps Usercentrics block the Facebook Pixel script loading via the "Meta for WooCommerce" plugin. The script will fire after a user consents. After enabling, be sure to test in a fresh incognito window.', 'termageddon-usercentrics' ),
				'beta'              => true,
				'default'           => false,
				'enqueue_script'    => false,
				'installed_plugins' => array( 'facebook-for-woocommerce/facebook-for-woocommerce.php' ),
			),
			'addtoany' => array(
				'name'              => __( 'AddToAny Share Buttons', 'termageddon-usercentrics' ),
				'description'       => __( 'Enabling this feature prevents AddToAny scripts from running until the visitor consents to the AddToAny service. AddToAny must also be present in your Usercentrics service settings. Test changes in a fresh incognito window.', 'termageddon-usercentrics' ),
				'beta'              => true,
				'default'           => false,
				'enqueue_script'    => false,
				'installed_plugins' => array( 'add-to-any/add-to-any.php' ),
			),
			'jetpack_stats' => array(
				'name'              => __( 'Jetpack', 'termageddon-usercentrics' ),
				'description'       => __( 'Enabling this feature prevents Jetpack tracking scripts from running until the visitor consents to the Jetpack service. Jetpack must also be present in your Usercentrics service settings. Test changes in a fresh incognito window.', 'termageddon-usercentrics' ),
				'beta'              => true,
				'default'           => false,
				'enqueue_script'    => false,
				'installed_plugins' => array( 'jetpack/jetpack.php' ),
			),
			'hubspot_plugin' => array(
				'name'            => __( 'HubSpot WordPress Plugin', 'termageddon-usercentrics' ),
				'description'     => __( 'Enabling this feature helps block the main HubSpot tracking script loaded by the HubSpot WordPress plugin. When enabled, this feature helps prevent the __hssrc, __hssc, __hstc, and hubspotutk cookies from being placed until the user clicks Accept. Test changes in a fresh incognito window.', 'termageddon-usercentrics' ),
				'beta'            => true,
				'default'         => false,
				'enqueue_script'  => false,
				'installed_plugins' => array( 'leadin/leadin.php' ),
			),
			'hubspot_forms' => array(
				'name'        => __( 'HubSpot Forms Compatibility', 'termageddon-usercentrics' ),
				'description' => __( 'When the HubSpot Forms service is marked as Marketing or Functional, forms will not load for users who click Deny. Enable this feature to add a blocker message that allows users to click Accept within the embedded form area. Test changes in a fresh incognito window.', 'termageddon-usercentrics' ),
				'beta'        => true,
				'default'     => false,
			),
			'uabb_video'      => array(
				'name'              => __( 'Ultimate Addons for Beaver Builder Video', 'termageddon-usercentrics' ),
				'description'       => __( 'This resolves and improves the cookie-consent implementation when using an image placeholder overlay for the Ultimate Addons for Beaver Builder video embed.', 'termageddon-usercentrics' ),
				'beta'              => false,
				'default'           => false,
				'installed_plugins' => array( 'bb-ultimate-addon/bb-ultimate-addon.php' ),
			),
		);
	}


	/**
	 * Check if the given integration is enabled.
	 *
	 * @param string $integration The slug of the integration to check.
	 * @return bool
	 */
	public static function is_integration_enabled( string $integration, bool $default = false ): bool {
		return get_option( 'termageddon_usercentrics_integration_' . $integration, $default ) ? true : false;
	}

	/**
	 * Check whether the plugin/theme related to an integration is installed.
	 *
	 * @param string $integration The slug of the integration to check.
	 * @return bool
	 */
	public static function is_integration_source_detected( string $integration ): bool {
		$integrations = self::get_integrations();
		if ( empty( $integrations[ $integration ] ) ) {
			return false;
		}

		$config = $integrations[ $integration ];

		foreach ( $config['installed_plugins'] ?? array() as $plugin_basename ) {
			if ( self::is_plugin_installed( $plugin_basename ) ) {
				return true;
			}
		}

		foreach ( $config['installed_themes'] ?? array() as $theme_stylesheet ) {
			if ( self::is_theme_installed( $theme_stylesheet ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check whether a plugin is installed.
	 *
	 * @param string $plugin_basename The plugin basename, e.g. leadin/leadin.php.
	 * @return bool
	 */
	public static function is_plugin_installed( string $plugin_basename ): bool {
		if ( ! function_exists( 'get_plugins' ) && defined( 'ABSPATH' ) ) {
			$plugin_functions = ABSPATH . 'wp-admin/includes/plugin.php';
			if ( file_exists( $plugin_functions ) ) {
				require_once $plugin_functions;
			}
		}

		if ( function_exists( 'get_plugins' ) ) {
			$plugins = get_plugins();
			return isset( $plugins[ $plugin_basename ] );
		}

		if ( defined( 'WP_PLUGIN_DIR' ) ) {
			return file_exists( trailingslashit( WP_PLUGIN_DIR ) . $plugin_basename );
		}

		return false;
	}

	/**
	 * Check whether a theme is installed.
	 *
	 * @param string $theme_stylesheet The theme stylesheet/slug.
	 * @return bool
	 */
	public static function is_theme_installed( string $theme_stylesheet ): bool {
		if ( function_exists( 'wp_get_theme' ) ) {
			$theme = wp_get_theme( $theme_stylesheet );
			return $theme && $theme->exists();
		}

		if ( defined( 'WP_CONTENT_DIR' ) ) {
			return is_dir( trailingslashit( WP_CONTENT_DIR ) . 'themes/' . $theme_stylesheet );
		}

		return false;
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

	/**
	 * Check if manual script control is enabled (disables auto-blocking)
	 *
	 * @return bool True if manual script control should be used, false otherwise
	 */
	public static function is_auto_blocker_disabled(): bool {
		return get_option( 'termageddon_usercentrics_disable_auto_blocker', false ) ? true : false;
	}

	/**
	 * Get the script snippets from the database
	 *
	 * @return array Array of snippet objects, each containing 'script' and 'service_id'
	 */
	public static function get_script_snippets(): array {
		return get_option( 'termageddon_usercentrics_script_snippets', array() );
	}

	/**
	 * Get the provider name by ID from the providers list
	 *
	 * @param string $service_id The provider ID to look up.
	 * @return string The provider name, or empty string if not found.
	 */
	public static function get_provider_name_by_id( string $service_id ): string {
		// Use cached providers from admin class to avoid duplicate loading
		if ( ! class_exists( 'Termageddon_Usercentrics_Admin' ) ) {
			require_once TERMAGEDDON_COOKIE_PATH . 'admin/class-termageddon-usercentrics-admin.php';
		}
		$providers = Termageddon_Usercentrics_Admin::get_usercentrics_providers();
		return $providers[ $service_id ] ?? '';
	}

	/**
	 * Augment script tags with Usercentrics blocking attributes
	 *
	 * Takes script HTML and augments all <script> tags with type="text/plain"
	 * and data-usercentrics attributes for cookie consent blocking.
	 *
	 * @param string $script_html The raw script HTML to augment.
	 * @param string $service_id  The provider ID to look up the service name.
	 * @return string The augmented script HTML, or original if service not found.
	 */
	public static function augment_script_for_usercentrics( string $script_html, string $service_id ): string {
		// Get service name from service_id
		$service_name = self::get_provider_name_by_id( $service_id );
		if ( empty( $service_name ) ) {
			// Log warning if service not found
			if ( function_exists( 'error_log' ) ) {
				error_log( sprintf( 'Termageddon Usercentrics: Invalid service_id "%s" provided to augment_script_for_usercentrics', $service_id ) );
			}
			return $script_html; // Return original if service not found
		}

		// Pattern to match script tags (both with src and inline)
		$pattern = '/<script([^>]*)>([\s\S]*?)<\/script>/i';

		$augmented = preg_replace_callback(
			$pattern,
			function( $matches ) use ( $service_name ) {
				$attributes = trim( $matches[1] );
				$content    = $matches[2];

				// Replace or add type="text/plain"
				if ( preg_match( '/type\s*=\s*["\'][^"\']*["\']/i', $attributes ) ) {
					// Replace existing type attribute
					$attributes = preg_replace(
						'/type\s*=\s*["\'][^"\']*["\']/i',
						'type="text/plain"',
						$attributes
					);
				} else {
					// Add type="text/plain" attribute
					$attributes = ( $attributes ? $attributes . ' ' : '' ) . 'type="text/plain"';
				}

				// Add or update data-usercentrics attribute
				$usercentrics_attr = 'data-usercentrics="' . esc_attr( $service_name ) . '"';
				if ( preg_match( '/data-usercentrics\s*=\s*["\'][^"\']*["\']/i', $attributes ) ) {
					// Replace existing data-usercentrics
					$attributes = preg_replace(
						'/data-usercentrics\s*=\s*["\'][^"\']*["\']/i',
						$usercentrics_attr,
						$attributes
					);
				} else {
					// Add data-usercentrics attribute
					$attributes .= ' ' . $usercentrics_attr;
				}

				// Tell the Smart Data Protector to leave this tag alone. The SDP matches
				// script src URLs against its own service list (e.g. googletagmanager.com/gtag/js
				// => "Google Analytics 4") and ignores data-usercentrics, so without this the
				// SDP re-blocks the tag under a service that may not exist in the configuration
				// and it never unblocks after consent.
				if ( ! preg_match( '/(^|\s)data-uc-untouch(\s|=|$)/i', $attributes ) ) {
					$attributes .= ' data-uc-untouch';
				}

				return '<script' . ( $attributes ? ' ' . $attributes : '' ) . '>' . $content . '</script>';
			},
			$script_html
		);

		$service_name_upper = strtoupper( $service_name );
		return PHP_EOL . '<!-- ' . esc_html( $service_name_upper ) . ' SCRIPT -->' . PHP_EOL . ( $augmented ?? $script_html ) . PHP_EOL;
	}

}
