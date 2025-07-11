<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://termageddon.com
 * @since      1.0.0
 *
 * @package    Termageddon_Usercentrics
 * @subpackage Termageddon_Usercentrics/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Termageddon_Usercentrics
 * @subpackage Termageddon_Usercentrics/public
 * @author     Termageddon <support@termageddon.com>
 */
class Termageddon_Usercentrics_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the scripts for the public area.
	 *
	 * @since    1.0.4
	 */
	public function enqueue_scripts() {

		// Load AJAX Mode scripts.
		if ( Termageddon_Usercentrics::is_ajax_mode_enabled() ) {
			wp_enqueue_script( $this->plugin_name . '_ajax', TERMAGEDDON_COOKIE_URL . 'public/js/termageddon-usercentrics-ajax.min.js', array( 'jquery' ), $this->version, false );

			// Load ajax params for nonce.
			$nonce    = wp_create_nonce( $this->plugin_name . '_ajax_nonce' );
			$location = get_query_var( 'termageddon-usercentrics-debug' );

			$data = array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'nonce'       => $nonce,
				'nonce_title' => $this->plugin_name . '_ajax_nonce',
				'debug'       => Termageddon_Usercentrics::is_debug_mode_enabled() ? 'true' : 'false',
				'psl_hide'    => Termageddon_Usercentrics::should_hide_psl() ? 'true' : 'false',
			);
			if ( ! empty( $location ) ) {
				$data['location'] = $location;
			}

			wp_localize_script(
				$this->plugin_name . '_ajax',
				'termageddon_usercentrics_obj',
				$data
			);
		}

		// Load Alternate PSL Logic.
		if ( Termageddon_Usercentrics::should_use_alternate_psl() ) {
			add_action( 'wp_footer', array( $this, 'replace_usercentrics_psl_with_shortcode' ) );
		}

		// Check for requirement of needing jQuery.
		if ( Termageddon_Usercentrics::is_integration_enabled( 'divi_video' )
		  || Termageddon_Usercentrics::is_integration_enabled( 'elementor_video' )
		  || Termageddon_Usercentrics::should_use_alternate_psl()
		) {
			wp_enqueue_script( 'jquery' );
		}

		// Load advanced configuration if needed
		$disabled_blocking_providers = Termageddon_Usercentrics::get_disabled_blocking_providers();
		$auto_refresh_providers = Termageddon_Usercentrics::get_auto_refresh_providers();

		if ( ! empty( $disabled_blocking_providers ) || ! empty( $auto_refresh_providers ) ) {
			wp_enqueue_script( 
				$this->plugin_name . '-advanced-config', 
				TERMAGEDDON_COOKIE_URL . 'public/js/termageddon-usercentrics-advanced-config.min.js', 
				array(), 
				$this->version, 
				false 
			);

			wp_localize_script(
				$this->plugin_name . '-advanced-config',
				'termageddon_usercentrics_advanced_config',
				array(
					'disabledBlockingProviders' => $disabled_blocking_providers,
					'autoRefreshProviders' => $auto_refresh_providers,
				)
			);
		}
	}


	/**
	 * Dynamically hide the termageddon script if termageddon should be disabled.
	 *
	 * @return void
	 */
	public function disable_termageddon_script() {
		$script = '';
		if ( Termageddon_Usercentrics::should_hide_psl() ) {
			$script .= '
		<style id="usercentrics-psl-hide">
			#usercentrics-psl,.usercentrics-psl {display:none;}
		</style>
		';
		}

		// Output to HTML HEAD.
		echo '<!-- TERMAGEDDON + USERCENTRICS (DISABLED) -->';
		echo wp_kses( $script, Termageddon_Usercentrics::ALLOWED_HTML );
		echo '<!-- END TERMAGEDDON + USERCENTRICS -->';
	}

	/**
	 * Disable the termageddon enqueue if the psl should be hidden.
	 *
	 * @return void
	 */
	public function disable_termageddon_enqueue() {
		if ( Termageddon_Usercentrics::should_hide_psl() ) {
			wp_enqueue_style( $this->plugin_name . '_disable', TERMAGEDDON_COOKIE_URL . 'public/css/termageddon-usercentrics-disable.min.css', array(), $this->version );
		}
	}

	/**
	 * Display debug information to console if applicable
	 *
	 * @return void
	 */
	public function debug_display() {
		if (
			Termageddon_Usercentrics::is_geoip_enabled() &&
			Termageddon_Usercentrics::is_debug_mode_enabled() &&
			! Termageddon_Usercentrics::is_ajax_mode_enabled()
		) {
			list('city' => $city, 'state' => $state, 'country' => $country) = Termageddon_Usercentrics::lookup_ip_address();

			// Iterate through locations.
			$locations = array();
			foreach ( Termageddon_Usercentrics::get_geolocation_locations() as $loc_key => $loc ) {
				list ( 'title' => $loc_name ) = $loc;
				$locations[]                  = 'Located in ' . $loc_name . ': ' . ( Termageddon_Usercentrics::is_located_in( $loc_key ) ? 'Yes' : 'No' );
			}

			// Output debug message to console.
			Termageddon_Usercentrics::debug(
				'IP Address: ' . Termageddon_Usercentrics::get_processed_ip_address(),
				'City: ' . ( $city ?? 'Unknown' ),
				'State: ' . ( $state ?? 'Unknown' ),
				'Country: ' . ( $country ?? 'Unknown' ),
				'--',
				$locations,
				'--',
				'Geo-Location Mode?: ' . ( Termageddon_Usercentrics::is_geoip_enabled() ? 'Yes' : 'No' ),
				'AJAX Mode?: ' . ( Termageddon_Usercentrics::is_ajax_mode_enabled() ? 'Yes' : 'No' )
			);
		}
	}


	/**
	 * Action to allow replacing a broken psl with the fully functional psl.
	 *
	 * @return void  */
	public function replace_usercentrics_psl_with_shortcode() {
		ob_start();
		?>
		<script id="termageddon-psl-alternate-js">
			(function($) {
				$(document).ready(function() {
					jQuery('a#usercentrics-psl,.usercentrics-psl a').each(function() {
						let newElem = jQuery(`<?php echo do_shortcode( '[uc-privacysettings]' ); ?>`);
						if (!["","Privacy Settings"].includes(jQuery(this).text())) newElem.text(jQuery(this).text())
						jQuery(this).replaceWith(newElem);
					})
					jQuery('button#usercentrics-psl,.usercentrics-psl button').each(function() {
						let newElem = jQuery(`<?php echo do_shortcode( '[uc-privacysettings type="button"]' ); ?>`);
						if (!["","Privacy Settings"].includes(jQuery(this).text())) newElem.text(jQuery(this).text())
						jQuery(this).replaceWith(newElem);
					})
				})
			})(jQuery);
		</script>
		<?php
		ob_end_flush();
	}


	/**
	 * Dynamically hide or show the termageddon script based on settings. Outputs directly to script tag.
	 */
	public function build_termageddon_script( $is_enqueue = false ) {

		// If forcibly enabled, bypass individual detections.
		if ( ! Termageddon_Usercentrics::is_enabled_via_get_override() ) {
			// Check for Disable for troubleshooting.
			if ( Termageddon_Usercentrics::is_disabled_for_troubleshooting() ) {
				return;
			}

			// Check for individual disable detections.
			$disable_on_logged_in = get_option( 'termageddon_usercentrics_disable_logged_in', false ) ? true : false;
			if ( $disable_on_logged_in && is_user_logged_in() ) {
				return;
			}

			$disable_on_editor = get_option( 'termageddon_usercentrics_disable_editor', false ) ? true : false;
			if ( $disable_on_editor && current_user_can( 'editor' ) ) {
				return;
			}

			$disable_on_admin = get_option( 'termageddon_usercentrics_disable_admin', false ) ? true : false;
			if ( $disable_on_admin && current_user_can( 'administrator' ) ) {
				return;
			}

			if ( Termageddon_Usercentrics::is_geoip_enabled() && ! Termageddon_Usercentrics::is_ajax_mode_enabled() && Termageddon_Usercentrics::should_hide_due_to_location() ) {
				return;
			}

			// don't double output in enqueue mode
			$should_enqueue_scripts = Termageddon_Usercentrics::get_embed_injection_method() === 'wp_enqueue_scripts';
			if ( $should_enqueue_scripts && ! $is_enqueue ) {
				return;
			}
		}

		$should_append_settings_id_embed_code = ! empty( Termageddon_Usercentrics::get_settings_id() ) ? true : false;

		$script = Termageddon_Usercentrics::get_embed_code(
			array(
				'filter_standard_embed_code' => true,
				'force_include_embed_code'   => $should_append_settings_id_embed_code,
			)
		);

		//Append a disabling script if geoip is enabled and ajax mode is enabled to disable flashing.
		if ( Termageddon_Usercentrics::is_geoip_enabled() && Termageddon_Usercentrics::is_ajax_mode_enabled() ) {
			$script .= '<script type="application/javascript">var UC_UI_SUPPRESS_CMP_DISPLAY = true;</script>';
		}

		if ( empty( $script ) ) {
			return;
		}

		// Output to HTML HEAD.
		$output  = '<!-- TERMAGEDDON + USERCENTRICS -->' . PHP_EOL;
		$output .= Termageddon_Usercentrics::processEmbedCode( $script );
		$output .= '<!-- END TERMAGEDDON + USERCENTRICS -->' . PHP_EOL;

		if ( $is_enqueue ) {
			return $output;
		} else {
			
			echo $output;
		}

	}
	/**
	 * Dynamically hide or show the termageddon script based on settings. Outputs directly to script tag.
	 */
	public function build_termageddon_enqueue() {
		
		$settings_id            = Termageddon_Usercentrics::get_settings_id();
		$should_enqueue_scripts = Termageddon_Usercentrics::get_embed_injection_method() === 'wp_enqueue_scripts';

		if ( $settings_id && $should_enqueue_scripts ) {
			// Enqueue Embed Script.
			wp_enqueue_script( $this->plugin_name . '-scripts', '//privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js', array(), $this->version, false );
			// note: this URL is placed here to "play by the rules"... but it doesn't actually do anything.
			// the whole thing will be overwritten by the script_loader_tag filter.
		}

		foreach ( Termageddon_Usercentrics::get_integrations() as $integration => $integration_config ) {
			if ( Termageddon_Usercentrics::is_integration_enabled( $integration, $integration_config['default'] ) ) {
				$slug = str_replace( '_', '-', $integration );
				wp_enqueue_script( $this->plugin_name . '-integration-' . $slug, TERMAGEDDON_COOKIE_URL . 'public/js/termageddon-usercentrics-integration-' . $slug . '.min.js', array(), $this->version, array() );
			}
		}

	}

	/**
	 * Filter the script loader tag to add the correct attributes to the script tags for Usercentrics.
	 *
	 * @param string $tag    The full HTML tag for the script.
	 * @param string $handle The script handle/ID.
	 * @param string $src    The script source URL.
	 * @return string The modified script tag.
	 */
	public function filter_script_loader_tag( $tag, $handle, $src ) {
		if ( $this->plugin_name . '-scripts' === $handle ) {
			$tag = self::build_termageddon_script( true );
		}
		return $tag;
	}

}
