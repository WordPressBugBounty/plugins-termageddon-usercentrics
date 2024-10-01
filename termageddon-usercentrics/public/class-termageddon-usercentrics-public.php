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
			wp_enqueue_script( $this->plugin_name . '_ajax', plugin_dir_url( __FILE__ ) . 'js/termageddon-usercentrics-ajax.min.js', array( 'jquery' ), $this->version, false );

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
	 * Display debug information to console if applicable
	 *
	 * @return void
	 */
	public function debug_display() {
		if ( ( Termageddon_Usercentrics::is_geoip_enabled() || Termageddon_Usercentrics::is_debug_mode_enabled() )
			&&
			! Termageddon_Usercentrics::is_ajax_mode_enabled()
			) {
				list('city' => $city, 'state' => $state, 'country' => $country) = Termageddon_Usercentrics::lookup_ip_address();

				// Iterate through locations.
				$locations = array();
				foreach (Termageddon_Usercentrics::get_geolocation_locations() as $loc_key => $loc) {
					list ( 'title' => $loc_name ) = $loc;
					$locations[] = 'Located in ' . $loc_name . ': ' . ( Termageddon_Usercentrics::is_located_in( $loc_key ) ? 'Yes' : 'No' );
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
					'AJAX Mode?: ' . ( Termageddon_Usercentrics::is_ajax_mode_enabled() ? 'Yes' : 'No' ),
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
				})
			})(jQuery);
		</script>
		<?php
		ob_end_flush();
	}


	/**
	 * Dynamically hide or show the termageddon script based on settings. Outputs directly to script tag.
	 */
	public function build_termageddon_script() {
		$script = get_option( 'termageddon_usercentrics_embed_code' );
		if ( empty( $script ) ) {
			return self::disable_termageddon_script();
		}

		// If forcibly enabled, bypass individual detections.
		if ( ! Termageddon_Usercentrics::is_enabled_via_get_override() ) {
			// Check for Disable for troubleshooting.
			if ( Termageddon_Usercentrics::is_disabled_for_troubleshooting() ) {
				return self::disable_termageddon_script();
			}

			// Debug display to console if applicable.
			self::debug_display();

			// Check for individual disable detections.
			$disable_on_logged_in = get_option( 'termageddon_usercentrics_disable_logged_in', false ) ? true : false;
			if ( $disable_on_logged_in && is_user_logged_in() ) {
				return self::disable_termageddon_script();
			}

			$disable_on_editor = get_option( 'termageddon_usercentrics_disable_editor', false ) ? true : false;
			if ( $disable_on_editor && current_user_can( 'editor' ) ) {
				return self::disable_termageddon_script();
			}

			$disable_on_admin = get_option( 'termageddon_usercentrics_disable_admin', false ) ? true : false;
			if ( $disable_on_admin && current_user_can( 'administrator' ) ) {
				return self::disable_termageddon_script();
			}

			if ( Termageddon_Usercentrics::is_geoip_enabled() && ! Termageddon_Usercentrics::is_ajax_mode_enabled() && Termageddon_Usercentrics::should_hide_due_to_location() ) {
				return self::disable_termageddon_script();
			}
		} else {
			// Debug display to console if applicable.
			self::debug_display();
		}

		if ( Termageddon_Usercentrics::is_geoip_enabled() && Termageddon_Usercentrics::is_ajax_mode_enabled() ) {
			$script .= '<script type="application/javascript">
			var UC_UI_SUPPRESS_CMP_DISPLAY=true;
		  </script>';
		}

		// Divi Video Player Integration Javascript.
		if ( Termageddon_Usercentrics::is_integration_enabled( 'divi_video' ) ) {
			$script .= '<script type="application/javascript" id="uc-integration-divi-video">
window.addEventListener(\'load\', function () {
	jQuery(\'div.et_pb_video_overlay_hover\').on(\'click\', function(e) {
		jQuery(this).closest(\'div.et_pb_video_overlay\').hide()
	}).find(\'a.et_pb_video_play\').attr(\'href\', \'javascript:void(0)\')
	})
</script>';
		}

		// Elementor Video Player Integration Javascript.
		if ( Termageddon_Usercentrics::is_integration_enabled( 'elementor_video' ) ) {
			$script .= '<script type="application/javascript" id="uc-integration-elementor-video">
window.addEventListener(\'load\', function () {
	jQuery(\'.pp-media-overlay\').on(\'click\', function(e) {
		jQuery(this).hide()
	})
</script>';
		}

		// Presto Player Integration Javascript.
		if ( Termageddon_Usercentrics::is_integration_enabled( 'presto_player' ) ) {
			$script .= '<script type="application/javascript" id="uc-integration-presto-player">
	function uc_integration_setup(iID,service) {
		uc.blockElements({[iID] : \'figure.presto-block-video.presto-provider-\'+service});
		uc.reloadOnOptIn(iID);
		uc.reloadOnOptOut(iID);
	}
	uc_integration_setup("BJz7qNsdj-7","youtube"); // Youtube
	uc_integration_setup("HyEX5Nidi-m","vimeo"); // Vimeo
</script>';
		}

		// Output to HTML HEAD.
		echo '<!-- TERMAGEDDON + USERCENTRICS -->';
		echo wp_kses( $script, Termageddon_Usercentrics::ALLOWED_HTML );
		echo '<!-- END TERMAGEDDON + USERCENTRICS -->';

	}

}
