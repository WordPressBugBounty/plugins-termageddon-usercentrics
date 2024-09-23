<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://termageddon.com
 * @since      1.0.0
 *
 * @package    Termageddon_Usercentrics
 * @subpackage Termageddon_Usercentrics/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Termageddon_Usercentrics
 * @subpackage Termageddon_Usercentrics/admin
 * @author     Termageddon <support@termageddon.com>
 */
class Termageddon_Usercentrics_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The current tab if on the admin settings page.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $current_tab;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// Calculate current tab.
		$default_tab = 'embed';
		$tab         = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : $default_tab;

		// Set property on object.
		$this->current_tab = $tab;
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		// Load CSS styles for admin use only.
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/termageddon-usercentrics-admin.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the scripts for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// Load JS styles for admin use only.
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/termageddon-usercentrics-admin.min.js', array( 'jquery-ui-core', 'jquery-ui-tabs' ), $this->version, false );

	}

		/**
		 * Add in options page compatibility
		 *
		 * @return void
		 */
	public function admin_page_config() {
		$page_suffix = add_management_page(
			'Termageddon + Usercentrics', // Page Title.
			'Termageddon + Usercentrics', // Menu link text.
			'manage_options', // Capability to access the page.
			'termageddon-usercentrics', // Page URL slug.
			array( &$this, 'admin_page_content' ), // Callback function /w content.
			5 // Priority.
		);

		// Load admin scripts ONLY when applicable.
		add_action(
			'load-' . $page_suffix,
			function() {
				add_action(
					'admin_enqueue_scripts',
					array( $this, 'enqueue_scripts' )
				);
			}
		);
	}

		/**
		 * The page content for the termageddon options page
		 *
		 * @return void  */
	public function admin_page_content() {
		// check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Display Error if geolocation failed.
		if ( Termageddon_Usercentrics::is_geoip_enabled() && Termageddon_Usercentrics::check_for_download_errors() ) {
			echo '<div class="notice notice-error">
				<p><strong>' . esc_html__( 'We were unable to download the database necessary for geolocation to your website. If you would like to use geolocation, please contact support for assistance and troubleshooting.', 'termageddon-usercentrics' ) . '</strong></p>
			</div>';
		}

		// Check for geolocation enabled, but no locations enabled.
		if ( Termageddon_Usercentrics::is_geoip_enabled() && ! Termageddon_Usercentrics::is_geoip_location_enabled() ) {
			echo '<div class="notice notice-warning" id="no-geolocation-locations-selected-top">
				<p><strong>' . esc_html__( 'Geo-Location is enabled, but no locations have been toggled on. This means that the cookie-consent will be hidden to all users.', 'termageddon-usercentrics' ) . '</strong></p>
				<p><a href="?page=termageddon-usercentrics&tab=geolocation">' . esc_html__( 'Please go to geo-location settings to enable at least one location or to disable the geo-location feature.', 'termageddon-usercentrics' ) . '</a></p>
			</div></a>';
		}

		// Display Estimated Location.
		if ( Termageddon_Usercentrics::is_geoip_enabled() && Termageddon_Usercentrics::is_debug_mode_enabled() ) {
			echo '<div class="notice notice-info">
				<p><strong>' . esc_html__( 'Location logging is enabled.', 'termageddon-usercentrics' ) . '</strong><br><br><strong>' . esc_html__( 'Your location', 'termageddon-usercentrics' ) . ':</strong><br> <em>' . esc_html( Termageddon_Usercentrics::get_location_displayname() ) . '</em> ' . esc_html__( 'with the IP Address of', 'termageddon-usercentrics' ) . ' <em>' . esc_html( Termageddon_Usercentrics::get_processed_ip_address() ) . '</em></p>
			</div>';
		}

		echo '<div class="wrap">
			<h1>' . esc_html__( 'Termageddon + Usercentrics', 'termageddon-usercentrics' ) . '</h1>';

			  // Get the active tab from the $_GET param.

		// Invalid tab error message.
		if ( ! in_array( $this->current_tab, array( 'embed', 'settings', 'geolocation', 'admin' ), true ) ) {
			echo '<div class="error notice">' . esc_html__( 'Invalid tab. Please check the link and try again.', 'termageddon-usercentrics' ) . '</div>';
			return;
		}

		settings_errors(); // Allow for notices.

		?>
		<nav class="nav-tab-wrapper">
			<a href="?page=termageddon-usercentrics" class="nav-tab <?php echo ( 'embed' === $this->current_tab ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e( 'Embed Code', 'termageddon-usercentrics' ); ?></a>
			<a href="?page=termageddon-usercentrics&tab=settings" class="nav-tab <?php echo( 'settings' === $this->current_tab ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e( 'Settings', 'termageddon-usercentrics' ); ?></a>
			<a href="?page=termageddon-usercentrics&tab=geolocation" class="nav-tab <?php echo ( 'geolocation' === $this->current_tab ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e( 'Geo-Location', 'termageddon-usercentrics' ); ?></a>
		</nav>

		<div class="tab-content">
		<?php
		if ( 'admin' === $this->current_tab ) {
			echo '<h2>' . esc_html__( 'Advanced Configuration & Troubleshooting', 'termageddon-usercentrics' ) . '</h2><p>' . esc_html__( 'This panel is for advanced configuration and troubleshooting. Please contact support before using any features on this panel.', 'termageddon-usercentrics' ) . '</p>';

			echo '<h3>' . esc_html__( 'Debug Information', 'termageddon-usercentrics' ) . '</h3>';
			$message_list   = array();
			$message_list[] = esc_html__( 'Geolocation Database Path', 'termageddon-usercentrics' ) . ':' . PHP_EOL . Termageddon_Usercentrics::get_maxmind_db_path();
			$message_list[] = esc_html__( 'Geolocation Database Exists', 'termageddon-usercentrics' ) . ':' . PHP_EOL . ( file_exists( Termageddon_Usercentrics::get_maxmind_db_path() ) ? 'true' : 'false' );
			$message_list[] = esc_html__( 'Geolocation Database Readable', 'termageddon-usercentrics' ) . ':' . PHP_EOL . ( is_readable( Termageddon_Usercentrics::get_maxmind_db_path() ) ? 'true' : 'false' );
			$message_list[] = esc_html__( 'Geolocation Database Directory Writable', 'termageddon-usercentrics' ) . ':' . PHP_EOL . ( is_writable( dirname( Termageddon_Usercentrics::get_maxmind_db_path() ) ) ? 'true' : 'false' );
			$message_list[] = esc_html__( 'Geolocation Database Last Updated', 'termageddon-usercentrics' ) . ': ' . PHP_EOL . Termageddon_Usercentrics::get_maxmind_db_last_updated();
			$message_list[] = esc_html__( 'Geolocation Database Next Update', 'termageddon-usercentrics' ) . ': ' . PHP_EOL . Termageddon_Usercentrics::get_maxmind_db_next_update();
			$message_list[] = esc_html__( 'Allowed HTML Tags', 'termageddon-usercentrics' ) . ': ' . PHP_EOL . Termageddon_Usercentrics::get_allowed_html_kses();
			echo '<textarea readonly rows="17" style="width: 100%;">' . esc_textarea( implode( PHP_EOL . PHP_EOL, $message_list ) ) . '</textarea>';

			if ( Termageddon_Usercentrics::count_download_errors() > 0 ) {
				echo '<h3>' . esc_html__( 'Error Information', 'termageddon-usercentrics' ) . '</h3>';
				$message_list   = array();
				$message_list[] = 'Geolocation Error Count: ' . PHP_EOL . Termageddon_Usercentrics::count_download_errors();
				$message_list[] = 'Geolocation Error Logs: ' . PHP_EOL . implode( PHP_EOL, Termageddon_Usercentrics::get_download_error_logs() );

				echo '<textarea readonly rows="15" style="width: 100%;">' . esc_textarea( implode( PHP_EOL . PHP_EOL, $message_list ) ) . '</textarea>';
			}
		}
		echo '<form method="post" action="options.php">';
				settings_fields( 'termageddon_usercentrics_settings' ); // Settings group name.
				do_settings_sections( 'termageddon-usercentrics' ); // page slug.
				submit_button();
		echo '
				</form>
			</div>';

		echo '</div>';

	}


	/** Build the default arguments for the current section
	 *
	 * @param mixed $tab The tab you are setting up; For use with identifying current tab and hiding/showing the tab.
	 * @return string[]
	 */
	public function build_section_args( $tab ) {

		// Lookup whether tab is active or not.
		$tab_active = ( $this->current_tab === $tab );

		return array(
			'before_section' => '<div class="tu-settings-tab tu-tab-' . $tab . ( $tab_active ? '' : ' tu-section-hidden' ) . '" >', // Start a new tab.
			'after_section'  => '</div></div>
			</div>', // Finish the additional sections setup in the html callbacks (2) and then finish the tab for a total of 3 divs.
		);
	}


	/**
	 * Generate and appends the HTML for a new subsection based on options provided.
	 *
	 * @param string $section The section slug to add the section to.
	 * @param array  $options name, description, indent, slim; Various options.
	 * @return void
	 */
	public function add_new_subsection( string $section, array $options = array() ) {

		$name        = ( isset( $options['name'] ) ? $options['name'] : false );
		$description = ( isset( $options['description'] ) ? $options['description'] : false );
		$indent      = ( isset( $options['indent'] ) ? true === $options['indent'] : false );
		$slim        = ( isset( $options['slim'] ) ? true === $options['slim'] : false );

		add_settings_field(
			'termageddon_usercentrics_end-section-' . Termageddon_Usercentrics::generate_random_string( 5 ),
			'	</th></tr></tbody></table>
			</div>
			<div class="tu-toggle-section">
				<span class="tu-section-title">' . esc_html( $name ) . ':</span>
				' . ( empty( $description ) ? '' : '<p>' . wp_kses_post( $description ) . '</p>' ) . '
			</div>
			<div class="' . ( $indent ? 'tu-toggle-section' : 'tu-settings-section' ) . ( $slim ? ' slim-section' : '' ) . '">
				<table class="form-table" role="presentation">
					<tbody>
						<tr style="display: none">
							<th scope="row">',
			function() {}, // function which prints the field.
			'termageddon-usercentrics', // page slug.
			$section, // section ID.
			array()
		);
	}


	/** Generates and appends the beta label for a field.
	 *
	 *  @return string  */
	public static function mark_as_beta() {
		return ' <span class="tu-label-warning">BETA</span>';
	}


	/**
	 * Buildout all settings sections in prep for registering the settings.
	 *
	 * @return void  */
	public function register_all_settings() {

		$this->register_settings_embed();
		$this->register_settings_settings();
		$this->register_settings_geolocation();
		$this->register_settings_admin();

	}

	/**
	 * Sanitize all settings and validate them if needed.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return int $value The sanitized value.
	 */
	public static function sanitize_integer( $value ) {
		return intval( $value );
	}
	/**
	 * Sanitize all settings and validate them if needed.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return string $value The sanitized value.
	 */
	public static function sanitize_text( $value ) {
		return strval( $value );
	}
	/**
	 * Sanitize all settings and validate them if needed.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return bool $value The sanitized value.
	 */
	public static function sanitize_boolean( $value ) {
		return $value ? true : false;
	}

	// ============================================= //
	// ======== Custom Sanitation Functions ======== //
	// ============================================= //

	/**
	 * Ensure that the embed priority is an integer between 1 and 10.
	 * If not, return 1.
	 *
	 * @param mixed $value The value to sanitize.
	 *
	 * @return int $value The sanitized value.
	 **/
	public static function sanitize_embed_priority( $value ) {
		$priority = self::sanitize_integer( $value );
		if ( $priority <= 10 && $priority >= 1 ) {
			return intval( $value );
		}
		return 1;
	}

	/**
	 * Register all settings for the tools page.
	 *
	 * @return void
	 */
	public function register_settings_embed() {

		// Build Settings Sections.
		add_settings_section(
			'termageddon_usercentrics_section_embed', // section ID.
			'Embed Code', // title (if needed).
			array( &$this, 'embed_description_html' ), // callback function (if needed).
			'termageddon-usercentrics', // page slug.
			$this->build_section_args( 'embed' ) // before and after sections.
		);

		// Embed Code.
		add_settings_field(
			'termageddon_usercentrics_embed_code',
			__( 'Embed Code', 'termageddon-usercentrics' ),
			array( &$this, 'embed_code_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_embed', // section ID.
			array(
				'label_for' => 'termageddon_usercentrics_embed_code',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_embed_code', // option name.
			'' // sanitization function.
		);

	}


	/**
	 * Register all settings for the tools page.
	 *
	 * @return void
	 */
	public function register_settings_settings() {

		// Build Settings Sections.
		add_settings_section(
			'termageddon_usercentrics_section_settings', // section ID.
			__( 'Settings', 'termageddon-usercentrics' ), // title (if needed).
			array( &$this, 'settings_description_html' ), // callback function (if needed).
			'termageddon-usercentrics', // page slug.
			$this->build_section_args( 'settings' ) // before and after sections.
		);

		// Disable Admin.
		add_settings_field(
			'termageddon_usercentrics_disable_admin',
			__( 'Administrators', 'termageddon-usercentrics' ),
			array( &$this, 'disable_for_admin_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_settings', // section ID.
			array(
				'label_for' => 'termageddon_usercentrics_disable_admin',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_disable_admin', // option name.
			'' // sanitization function.
		);

		// Disable Editor.
		add_settings_field(
			'termageddon_usercentrics_disable_editor',
			__( 'Editors', 'termageddon-usercentrics' ),
			array( &$this, 'disable_for_editor_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_settings', // section ID.
			array(
				'label_for' => 'termageddon_usercentrics_disable_editor',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_disable_editor', // option name.
			'' // sanitization function.
		);

		// Disable Login.
		add_settings_field(
			'termageddon_usercentrics_disable_logged_in',
			__( 'Logged-in Users', 'termageddon-usercentrics' ),
			array( &$this, 'disable_for_logged_in_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_settings', // section ID.
			array(
				'label_for' => 'termageddon_usercentrics_disable_logged_in',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_disable_logged_in', // option name.
			'' // sanitization function.
		);

		// BREAK SECTION FOR PSL SETTINGS.
		$this->add_new_subsection(
			'termageddon_usercentrics_section_settings',
			array(
				'name'        => 'Privacy Settings Link',
				'description' => 'Giving users the ability to change their privacy/consent settings is a requirement under several privacy laws. Within your Termageddon account, if you selected (on page 1 of the Cookie Policy and Consent Tool questionnaire) to use the Privacy Settings hyperlink (instead of the \'fingerprint icon\'), you will see an additional script in the \'view embed code\' page that will need to be added to your website.</p>
				<p>As an alternative to that additional script, you can simply use the [uc-privacysettings] shortcode, which will embed the privacy settings link. This is an easy way to add the Privacy Settings link within your global footer, for example. You can also pass in a text parameter to change the text of the link (for example, <code>[uc-privacysettings text="Change Privacy Settings"]</code>).',
			)
		);

		// Privacy Settings Link Disable.
		add_settings_field(
			'termageddon_usercentrics_location_psl_hide',
			__( 'Hide Privacy Settings Link when cookie consent tool is disabled', 'termageddon-usercentrics' ),
			array( &$this, 'location_psl_disable_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_settings', // section ID.
			array(
				'label_for'   => 'termageddon_usercentrics_location_psl_hide',
				'description' => __( 'When enabled, the Privacy Settings link will be hidden from certain users, whether that be certain logged in users (via your selections above in the “Hide widget for:” area) or if you enabled geolocation and are hiding the consent tool from certain visitors. For example, if you enable the option to hide the consent tool for logged in administrator (and enable this toggle), the Privacy Settings link will <strong>not</strong> show to logged in administrators.  If you enable the option to hide the consent tool based on geolocation for certain users, the Privacy Settings link will no longer be displayed to those respective users as well.', 'termageddon-usercentrics' ),
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_location_psl_hide', // option name.
			'' // sanitization function.
		);

		// Privacy Settings Link Alternative Implementation.
		add_settings_field(
			'termageddon_usercentrics_psl_alternate',
			__( 'Privacy Settings Link - Alternative Implementation', 'termageddon-usercentrics' ),
			array( &$this, 'psl_alternate_implementation' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_settings', // section ID.
			array(
				'label_for'   => 'termageddon_usercentrics_psl_alternate',
				'description' => __( 'For some theme builders including Divi, the footer (bottom bar) does not support shortcodes. If you are experiencing issues, use the following HTML code where you want to the privacy settings link to be instead of the shortcode and enable this option:', 'termageddon-usercentrics' ) . '<br><code>&lt;a id=&quot;usercentrics-psl&quot;&gt;Privacy Settings&lt;/a&gt;</code>',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_psl_alternate', // option name.
			'' // sanitization function.
		);

		// BREAK SECTION FOR INTEGRATION SETTINGS.
		$this->add_new_subsection(
			'termageddon_usercentrics_section_settings',
			array(
				'name'        => 'Integrations',
				'description' => 'We love to partner with other plugin developers to improve support with their plugins. If you encounter an issue, please contact our support to let us know.',
			)
		);

		// Divi Video Overlay Integration Player.
		add_settings_field(
			'termageddon_usercentrics_integration_divi_video',
			__( 'Divi Video Player Integration', 'termageddon-usercentrics' ) . '<br>
			<em>' . __( 'This resolves and improves the cookie-consent implementation when using an image placeholder overlay for the Divi video embed.', 'termageddon-usercentrics' ) . '</em>',
			array( &$this, 'divi_video_support' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_settings', // section ID.
			array(
				'label_for' => 'termageddon_usercentrics_integration_divi_video',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_integration_divi_video', // option name.
			'' // sanitization function.
		);

		// Elementor Video Overlay Integration Player.
		add_settings_field(
			'termageddon_usercentrics_integration_elementor_video',
			__( 'Elementor Video Player Integration', 'termageddon-usercentrics' ) . $this->mark_as_beta() . '<br>
			<em>' . __( 'This resolves and improves the cookie-consent implementation when using an image placeholder overlay for the Elementor video embed.', 'termageddon-usercentrics' ) . '</em>',
			array( &$this, 'elementor_video_support' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_settings', // section ID.
			array(
				'label_for' => 'termageddon_usercentrics_integration_elementor_video',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_integration_elementor_video', // option name.
			'' // sanitization function.
		);

		// Presto Player.
		add_settings_field(
			'termageddon_usercentrics_integration_presto_player',
			__( 'Presto Player', 'termageddon-usercentrics' ),
			array( &$this, 'presto_player_support' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_settings', // section ID.
			array(
				'label_for' => 'termageddon_usercentrics_integration_presto_player',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_integration_presto_player', // option name.
			'' // sanitization function.
		);

		// BREAK SECTION FOR OTHER SETTINGS.
		$this->add_new_subsection(
			'termageddon_usercentrics_section_settings',
			array(
				'name'        => 'Other Settings',
				'description' => 'This section contains various other settings that can be adjusted for this plugin.',
			)
		);

		// Privacy Settings Link Disable.
		add_settings_field(
			'termageddon_usercentrics_embed_priority',
			__( 'Embed Code Priority', 'termageddon-usercentrics' ) . $this->mark_as_beta(),
			array( &$this, 'embed_priority_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_settings', // section ID.
			array(
				'label_for'   => 'termageddon_usercentrics_embed_priority',
				'description' => __( 'Override the default priority of the embed code (Defaults to 1). By adjusting this value (number between 1 and 10), you can change the priority of the embed code. The higher the number, the sooner the embed code will be rendered in the source code.', 'termageddon-usercentrics' ),
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_embed_priority', // option name.
			array(
				'type'              => 'int',
				'sanitize_callback' => array( &$this, 'sanitize_embed_priority' ),
				'default'           => 1,
			)
		);

		add_settings_field(
			'termageddon_usercentrics_disable_troubleshooting',
			__( 'Disable for Troubleshooting', 'termageddon-usercentrics' ),
			array( &$this, 'disable_troubleshooting_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_settings', // section ID.
			array(
				'label_for'   => 'termageddon_usercentrics_disable_troubleshooting',
				'description' => __( 'When enabled, this feature allows you to turn off the consent tool for all site visitors, however by adding <code>?enable-usercentrics</code> to the end of a URL, the consent tool will load, allowing you to troubleshoot any issues (or to reach out to Termageddon support to help assist with troubleshooting)', 'termageddon-usercentrics' ),
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_disable_troubleshooting', // option name.
			'' // sanitization function.
		);
	}

	/**
	 * Register all settings for the tools page.
	 *
	 * @return void
	 */
	public function register_settings_geolocation() {

		// Build Settings Sections.
		$is_checked = Termageddon_Usercentrics::is_geoip_enabled();
		add_settings_section(
			'termageddon_usercentrics_section_geolocation', // section ID.
			__( 'Geo-Location', 'termageddon-usercentrics' ) .
			' <input type="checkbox" class="termageddon-checkbox wppd-ui-toggle label-enabled" id="termageddon_usercentrics_toggle_geolocation" name="termageddon_usercentrics_geoip_enabled" value="1" ' . checked( 1, $is_checked, false ) . ' />', // title (if needed).
			array( &$this, 'settings_location_html' ), // callback function (if needed).
			'termageddon-usercentrics', // page slug.
			$this->build_section_args( 'geolocation' ) // before and after sections.
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_geoip_enabled', // option name.
			'' // sanitization function.
		);

		// ============================================ //
		// ======== Location specific settings ======== //
		// ============================================ //

		// Show only in EU.
		add_settings_field(
			'termageddon_usercentrics_show_in_eu',
			__( 'European Union & European Economic Area (GDPR)', 'termageddon-usercentrics' ),
			array( &$this, 'show_in_eu_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_geolocation', // section ID.
			array(
				'label_for' => 'termageddon_usercentrics_show_in_eu',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_show_in_eu', // option name.
			'' // sanitization function.
		);

		// Show only in UK.
		add_settings_field(
			'termageddon_usercentrics_show_in_uk',
			__( 'United Kingdom (UK DPA)', 'termageddon-usercentrics' ),
			array( &$this, 'show_in_uk_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_geolocation', // section ID.
			array(
				'label_for' => 'termageddon_usercentrics_show_in_uk',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_show_in_uk', // option name.
			'' // sanitization function.
		);

		// Show only in Canada.
		add_settings_field(
			'termageddon_usercentrics_show_in_canada',
			__( 'Canada (PIPEDA)', 'termageddon-usercentrics' ),
			array( &$this, 'show_in_canada_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_geolocation', // section ID.
			array(
				'label_for' => 'termageddon_usercentrics_show_in_canada',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_show_in_canada', // option name.
			'' // sanitization function.
		);

		// Show only in California.
		add_settings_field(
			'termageddon_usercentrics_show_in_california',
			__( 'California (CPRA or CIPA)', 'termageddon-usercentrics' ),
			array( &$this, 'show_in_california_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_geolocation', // section ID.
			array(
				'label_for' => 'termageddon_usercentrics_show_in_california',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_show_in_california', // option name.
			'' // sanitization function.
		);

		// Show only in Virginia.
		add_settings_field(
			'termageddon_usercentrics_show_in_virginia',
			__( 'Virginia (VCDPA)', 'termageddon-usercentrics' ),
			array( &$this, 'show_in_virginia_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_geolocation', // section ID.
			array(
				'label_for' => 'termageddon_usercentrics_show_in_virginia',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_show_in_virginia', // option name.
			'' // sanitization function.
		);

		// BREAK SECTION FOR GEOLOCATION SETTINGS.
		$this->add_new_subsection(
			'termageddon_usercentrics_section_geolocation',
			array(
				'name'        => 'Geo-Location Settings',
				'description' => 'If you are experiencing issues or unexpected behavior, toggle the "Enable Location Logging" option. Please contact our support if you have any questions.',
			)
		);

		// Enable Geolocation Debug Mode.
		add_settings_field(
			'termageddon_usercentrics_location_debug',
			__( 'Enable location logging', 'termageddon-usercentrics' ),
			array( &$this, 'location_debug_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_geolocation', // section ID.
			array(
				'label_for'   => 'termageddon_usercentrics_location_debug',
				'description' => __( 'When enabled, the visitor\'s location can be viewed in the browser console, allowing easier testing & troubleshooting.', 'termageddon-usercentrics' ),

			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_location_debug', // option name.
			'' // sanitization function.
		);

		// Enable Geolocation AJAX Mode.
		add_settings_field(
			'termageddon_usercentrics_location_ajax',
			__( 'Enable page caching support via AJAX', 'termageddon-usercentrics' ),
			array( &$this, 'location_ajax_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_geolocation', // section ID.
			array(
				'label_for'   => 'termageddon_usercentrics_location_ajax',
				'description' => __( 'When enabled, the visitor\'s location is checked via javascript to allow support for page caching.', 'termageddon-usercentrics' ),
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_location_ajax', // option name.
			'' // sanitization function.
		);

	}
	/**
	 * Register all settings for the admin tab.
	 *
	 * @return void
	 */
	public function register_settings_admin() {

		// Build Settings Sections.
		add_settings_section(
			'termageddon_usercentrics_section_admin', // section ID.
			__( 'Control Panel', 'termageddon-usercentrics' ) .
			'', // title (if needed).
			array( &$this, 'settings_admin_html' ), // callback function (if needed).
			'termageddon-usercentrics', // page slug.
			$this->build_section_args( 'admin' ) // before and after sections.
		);

		// ============================================ //
		// ======== Location specific settings ======== //
		// ============================================ //

		// Error Count Field.
		add_settings_field(
			'termageddon_usercentrics_download_error_count',
			__( 'Error Count', 'termageddon-usercentrics' ),
			array( &$this, 'error_count_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_admin', // section ID.
			array(
				'label_for' => 'termageddon_usercentrics_download_error_count',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_download_error_count', // option name.
			'' // sanitization function.
		);

		// Error List Field.
		add_settings_field(
			'termageddon_usercentrics_download_error_log',
			__( 'Error List', 'termageddon-usercentrics' ),
			array( &$this, 'error_list_html' ), // function which prints the field.
			'termageddon-usercentrics', // page slug.
			'termageddon_usercentrics_section_admin', // section ID.
			array(
				'label_for' => 'termageddon_usercentrics_download_error_log',
			)
		);

		register_setting(
			'termageddon_usercentrics_settings', // settings group name.
			'termageddon_usercentrics_download_error_log', // option name.
			'' // sanitization function.
		);

	}

	/**
	 * Helper method to easily generate a quick input field.
	 *
	 * @param string $option - The option name/location you are building the input for.
	 * @param array  $args The arguments provided by the add_settings_field() method.
	 * @return void
	 */
	private static function generate_input( string $option, array $args = array() ) {
		$option_name = 'termageddon_usercentrics_' . $option;

		// Options.
		$default     = ( isset( $args['default'] ) ? $args['default'] : null );
		$min         = ( isset( $args['min'] ) ? $args['min'] : null );
		$max         = ( isset( $args['max'] ) ? $args['max'] : null );
		$type        = ( isset( $args['type'] ) ? $args['type'] : 'text' );
		$label       = ( isset( $args['label'] ) ? $args['label'] : '' );
		$tip         = ( isset( $args['tip'] ) ? $args['tip'] : null );
		$description = ( isset( $args['description'] ) ? $args['description'] : null );

		// Is the option currently active?
		$value = get_option( $option_name, $default );

		echo '<input 
			type="' . esc_attr( $type ) . '" 
			class="termageddon-input' . ( ! empty( $label ) ? ' label-' . esc_attr( $label ) : '' ) . '"
			id="' . esc_attr( $option_name ) . '"
			name="' . esc_attr( $option_name ) . '"
			value="' . esc_attr( $value ) . '"
			' . ( is_null( $min ) ? '' : 'min="' . esc_attr( $min ) . '"' ) . '
			' . ( is_null( $max ) ? '' : 'max="' . esc_attr( $max ) . '"' ) . '
			 />';

		if ( $tip ) {
			echo '<b class="wntip" data-title="' . esc_attr( $tip ) . '"> ? </b>';
		}
		if ( $description ) {
			echo '<p>' . wp_kses_post( $description ) . '</p>';
		}

	}
	/**
	 * Helper method to easily generate a quick checkbox.
	 *
	 * @param string $area - The option name/location you are building the checkbox for.
	 * @param string $prefix - The prefix to the option.
	 * @param array  $args The arguments provided by the add_settings_field() method.
	 * @return void
	 */
	private static function generate_checkbox( string $area, string $prefix = 'show_in', array $args = array() ) {
		$option_name = 'termageddon_usercentrics_' . $prefix . '_' . $area;

		// Options.
		$default     = ( isset( $args['default'] ) ? $args['default'] : false );
		$label       = ( isset( $args['label'] ) ? $args['label'] : '' );
		$tip         = ( isset( $args['tip'] ) ? $args['tip'] : null );
		$description = ( isset( $args['description'] ) ? $args['description'] : null );

		// Is the option currently active?
		$is_checked = get_option( $option_name, $default ) ? true : false;

		echo '<input type="checkbox"  class="termageddon-checkbox wppd-ui-toggle' . ( ! empty( $label ) ? ' label-' . esc_attr( $label ) : '' ) . '" id="' . esc_attr( $option_name ) . '" name="' . esc_attr( $option_name ) . '" value="1" ' . checked( 1, $is_checked, false ) . ' />';

		if ( $tip ) {
			echo '<b class="wntip" data-title="' . esc_attr( $tip ) . '"> ? </b>';
		}
		if ( $description ) {
			echo '<p>' . wp_kses_post( $description ) . '</p>';
		}

	}

	/**
	 * The HTML field for the admin page for the checkbox.
	 *
	 * @param array $args The arguments provided by the add_settings_field() method.
	 * @return void
	 */
	public function disable_for_logged_in_html( array $args ) {
		$args['label'] = 'hidden';
		self::generate_checkbox( 'logged_in', 'disable', $args );
	}

	/**
	 * The HTML field for the editor disable checkbox.
	 *
	 * @param array $args The arguments provided by the add_settings_field() method.
	 * @return void
	 */
	public function disable_for_editor_html( array $args ) {
		$args['label'] = 'hidden';
		self::generate_checkbox( 'editor', 'disable', $args );
	}

	/**
	 * The HTML field for the admin disable checkbox.
	 *
	 * @param array $args The arguments provided by the add_settings_field() method.
	 * @return void
	 */
	public function disable_for_admin_html( array $args ) {
		$args['label'] = 'hidden';
		self::generate_checkbox( 'admin', 'disable', $args );
	}

	/**
	 * The HTML field for the admin disable checkbox.
	 *
	 * @param array $args The arguments provided by the add_settings_field() method.
	 * @return void
	 */
	public function divi_video_support( array $args ) {
		$args['label'] = 'enabled';
		self::generate_checkbox( 'divi_video', 'integration', $args );
	}

	/**
	 * The HTML field for the admin disable checkbox.
	 *
	 * @param array $args The arguments provided by the add_settings_field() method.
	 * @return void
	 */
	public function elementor_video_support( array $args ) {
		$args['label'] = 'enabled';
		self::generate_checkbox( 'elementor_video', 'integration', $args );
	}

	/**
	 * The HTML field for the admin disable checkbox.
	 *
	 * @param array $args The arguments provided by the add_settings_field() method.
	 * @return void
	 */
	public function presto_player_support( array $args ) {
		$args['label'] = 'enabled';
		self::generate_checkbox( 'presto_player', 'integration', $args );
	}

	/**
	 * The HTML field for the logging checkbox.
	 *
	 * @param array $args The arguments provided by the add_settings_field() method.
	 * @return void
	 */
	public function location_debug_html( array $args ) {
		self::generate_checkbox( 'debug', 'location', $args );
	}
	/**
	 * The HTML field for the psl hidden checkbox.
	 *
	 * @param array $args The arguments provided by the add_settings_field() method.
	 * @return void
	 */
	public function location_psl_disable_html( array $args ) {
		self::generate_checkbox( 'psl_hide', 'location', $args );
	}
	/**
	 * The HTML field for the psl alternate implementation checkbox.
	 *
	 * @param array $args The arguments provided by the add_settings_field() method.
	 * @return void
	 */
	public function psl_alternate_implementation( array $args ) {
		self::generate_checkbox( 'alternate', 'psl', $args );
	}
	/**
	 * The HTML field for the disable troubleshooting checkbox.
	 *
	 * @param array $args The arguments provided by the add_settings_field() method.
	 * @return void
	 */
	public function embed_priority_html( array $args ) {
		$args['default'] = 1;
		$args['min']     = 1;
		$args['max']     = 10;
		$args['type']    = 'number';

		self::generate_input( 'embed_priority', $args );
	}
	/**
	 * The HTML field for the disable troubleshooting checkbox.
	 *
	 * @param array $args The arguments provided by the add_settings_field() method.
	 * @return void
	 */
	public function disable_troubleshooting_html( array $args ) {
		self::generate_checkbox( 'troubleshooting', 'disable', $args );
	}
	/**
	 * The HTML field for the ajax mode checkbox.
	 *
	 * @param array $args The arguments provided by the add_settings_field() method.
	 * @return void
	 */
	public function location_ajax_html( array $args ) {
		$args['default'] = true;
		self::generate_checkbox( 'ajax', 'location', $args );
	}

	/**
	 * The HTML field for the admin disable checkbox.
	 *
	 * @return void
	 */
	public function show_in_eu_html() {
		self::generate_checkbox( 'eu' );
	}

	/**
	 * The HTML field for the admin disable checkbox.
	 *
	 * @return void
	 */
	public function show_in_uk_html() {
		self::generate_checkbox( 'uk' );

	}

	/**
	 * The HTML field for the admin disable checkbox.
	 *
	 * @return void
	 */
	public function show_in_canada_html() {
		self::generate_checkbox( 'canada' );

	}

	/**
	 * The HTML field for the admin disable checkbox.
	 *
	 * @return void
	 */
	public function show_in_california_html() {
		self::generate_checkbox( 'california' );

	}

	/**
	 * The HTML field for the admin disable checkbox.
	 *
	 * @return void
	 */
	public function show_in_virginia_html() {
		self::generate_checkbox( 'virginia' );

	}

	/**
	 * The embed code html for the text area field
	 *
	 * @return void
	 */
	public function embed_code_html() {

		$text = get_option( 'termageddon_usercentrics_embed_code' );

		printf(
			'<textarea class="termageddon-embed-code" type="text" id="termageddon_usercentrics_embed_code" name="termageddon_usercentrics_embed_code" placeholder="' . esc_attr( 'Your embed code should look similar to this', 'termageddon-usercentrics' ) . ':

&lt;link rel=&quot;preconnect&quot; href=&quot;//privacy-proxy.usercentrics.eu&quot;&gt;
&lt;link rel=&quot;preload&quot; href=&quot;//privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js&quot; as=&quot;script&quot;&gt;
&lt;script type=&quot;application/javascript&quot; src=&quot;https://privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js&quot;&gt;&lt;/script&gt;
&lt;script id=&quot;usercentrics-cmp&quot; src=&quot;https://app.usercentrics.eu/browser-ui/latest/loader.js&quot; data-settings-id=&quot;XXXXXXXXX&quot; async&gt;&lt;/script&gt;">%s</textarea>',
			esc_textarea( $text )
		);

	}

	/**
	 * The error count html for the error code field
	 *
	 * @return void
	 */
	public function error_count_html() {

		$text = get_option( 'termageddon_usercentrics_download_error_count', 0 );
		printf(
			'<input type="text" id="termageddon_usercentrics_download_error_count" name="termageddon_usercentrics_download_error_count" value="%s" />',
			esc_attr( $text )
		);

	}

	/**
	 * The error list html for the error code field
	 *
	 * @return void
	 */
	public function error_list_html() {

		$text = get_option( 'termageddon_usercentrics_download_error_log', '' );
		echo '<input type="hidden" id="termageddon_usercentrics_download_error_log" name="termageddon_usercentrics_download_error_log[]" value="" />
		<p>' . esc_html__( 'Upon saving, all previous errors in the log will be deleted.', 'termageddon-usercentrics' ) . '</p>';

	}


	/**
	 * The embed code html for the text area field
	 *
	 * @return void
	 */
	public function embed_description_html() {

		echo '
		<p>' .
			esc_html__( 'Upon generating your ', 'termageddon-usercentrics' ) . '
			<strong>' . esc_html__( 'Cookie Policy and Consent Solution', 'termageddon-usercentrics' ) . '</strong> ' .
			esc_html__( 'within your Termageddon account, you will be brought to the "View embed code" page.  Copy the embed code listed under "Usercentrics cookie consent tool embedding instructions" and paste it below:', 'termageddon-usercentrics' ) . '</p>';

		echo '
		<div class="tu-section-settings">
			<div class="tu-section">';

	}

	/**
	 * The embed code html for the text area field
	 *
	 * @return void
	 */
	public function settings_description_html() {

		echo '<p>' .
		esc_html__( 'If you would like to remove Usercentrics for logged in users such as admins, you can do so below.', 'termageddon-usercentrics' ) .
		' <strong>' . esc_html__( 'If you are using the Divi theme', 'termageddon-usercentrics' ) . '</strong>, ' .
		esc_html__( 'you will need to enable at least one of the settings below to ensure logged in users/admins can properly use the “Enable Visual Builder” feature provided by Divi when editing the design of a webpage.', 'termageddon-usercentrics' ) . '
			</p>';

			echo '
			<div class="tu-section-settings">
				<div class="tu-toggle-section slim-section">
					<span class="tu-section-title">' . esc_html__( 'Hide widget for' ) . ':</span>';

	}
	/**
	 * The html for above the geolocation settings.
	 *
	 * @return void
	 */
	public function settings_location_html() {

		echo '<p>' .
			esc_html__( 'When enabled, you will be collecting IP addresses for the purposes of determining which cookie consent solution (or lack thereof) to provide to each website visitor (CPRA or CIPA cookie consent, GDPR cookie consent, UK DPA consent or none), based on their location. A cookie will then be placed on the user\'s browser to cache their location, which helps improve page load speed when the user visits other pages on the website. You should ensure that you are in compliance with all applicable privacy laws prior to using this feature (or any other technologies on your website).', 'termageddon-usercentrics' ) .
		'</p>';

		echo '<p>' .
			esc_html__( 'Not sure what to select? Review', 'termageddon-usercentrics' ) . ' <a href="https://termageddon.freshdesk.com/support/solutions/articles/66000503289-how-to-activate-a-cookie-policy-and-cookie-consent-solution" target="_blank">' . esc_html__( 'this article', 'termageddon-usercentrics' ) . '</a>' . ' ' . esc_html__( 'along with page 1 of your Privacy Policy questionnaire within ', 'termageddon-usercentrics' ) . '<a href="https://app.termageddon.com/home" target="_blank">app.termageddon.com</a>.' .
		'</p>';

			echo '
			<div class="tu-section-settings">
				<div class="tu-toggle-section">
					<div class="tu-section-title">' . esc_html__( 'Show widget if visitor is located in' ) . ':</div>
					<div class="tu-section-title-helper">' . esc_html__( 'Not sure what to select? Review page 1 of your Privacy Policy questionnaire within' ) . ' <a href="https://app.termageddon.com" target="_blank">app.termageddon.com</a>.</div>
					<div class="notice-inline notice-alt notice-warning" id="no-geolocation-locations-selected" style="display:none">
						<p><strong>' . esc_html__( 'Geo-Location is enabled, but no locations have been toggled on. This means that the cookie-consent will be hidden to all users.', 'termageddon-usercentrics' ) . '</strong></p>
					</div></a>';

	}

	/**
	 * The html for above the admin panel.
	 *
	 * @return void
	 */
	public function settings_admin_html() {

		echo '<p>' . esc_html__( 'To reset any of the backend variables, update the values below and save your changes.', 'termageddon-usercentrics' ) . '</p>';

		echo '
		<div class="tu-section-settings">
			<div class="tu-section">';

	}




}
