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

		// Load hosted geolocation when location-based display is enabled.
		if ( Termageddon_Usercentrics::is_geoip_enabled() ) {
			wp_enqueue_script( $this->plugin_name . '_ajax', TERMAGEDDON_COOKIE_URL . 'public/js/termageddon-usercentrics-ajax.min.js', array(), $this->version, false );

			$data = array(
				'debug'    => Termageddon_Usercentrics::is_debug_mode_enabled() ? 'true' : 'false',
				'psl_hide' => Termageddon_Usercentrics::should_hide_psl() ? 'true' : 'false',
			);
			$data = array_merge( $data, Termageddon_Usercentrics_Geo_Api::build_localization_payload() );

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

		// Note: jQuery dependency has been removed from all frontend scripts.
		// divi_video, elementor_video, AJAX geo-location, and PSL alternate
		// all use vanilla JS.

		// Load advanced configuration if needed.
		// Note: UC now loads for all visitors (including out-of-region with auto-accept),
		// so `uc` will always be defined. The JS-side typeof guard remains as defense-in-depth.
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
	 * Action to allow replacing a broken psl with the fully functional psl.
	 *
	 * @return void  */
	public function replace_usercentrics_psl_with_shortcode() {
		ob_start();
		?>
		<script id="termageddon-psl-alternate-js">
			(function() {
				function replacePSL() {
					var linkTemplate = document.createElement('template');
					linkTemplate.innerHTML = <?php echo wp_json_encode( do_shortcode( '[uc-privacysettings]' ) ); ?>;
					var buttonTemplate = document.createElement('template');
					buttonTemplate.innerHTML = <?php echo wp_json_encode( do_shortcode( '[uc-privacysettings type="button"]' ) ); ?>;

					var linkSource = linkTemplate.content.firstElementChild;
					var buttonSource = buttonTemplate.content.firstElementChild;

					if (linkSource) {
						document.querySelectorAll('a#usercentrics-psl, .usercentrics-psl a').forEach(function(el) {
							var newElem = linkSource.cloneNode(true);
							var text = el.textContent.trim();
							if (text !== '' && text !== 'Privacy Settings') newElem.textContent = text;
							el.replaceWith(newElem);
						});
					}
					if (buttonSource) {
						document.querySelectorAll('button#usercentrics-psl, .usercentrics-psl button').forEach(function(el) {
							var newElem = buttonSource.cloneNode(true);
							var text = el.textContent.trim();
							if (text !== '' && text !== 'Privacy Settings') newElem.textContent = text;
							el.replaceWith(newElem);
						});
					}
				}
				if (document.readyState === 'loading') {
					document.addEventListener('DOMContentLoaded', replacePSL);
				} else {
					replacePSL();
				}
			})();
		</script>
		<?php
		ob_end_flush();
	}


	/**
	 * Get augmented script snippets for output
	 *
	 * Retrieves script snippets from database, augments them with Usercentrics attributes,
	 * and returns the concatenated result.
	 *
	 * @return string The concatenated augmented script snippets.
	 */
	public function get_augmented_script_snippets(): string {
		$snippets = Termageddon_Usercentrics::get_script_snippets();
		if ( empty( $snippets ) || ! is_array( $snippets ) ) {
			return '';
		}

		$augmented_scripts = array();
		foreach ( $snippets as $snippet ) {
			// Validate snippet structure
			if ( ! is_array( $snippet ) || ! isset( $snippet['script'], $snippet['service_id'] ) ) {
				continue;
			}

			$script     = trim( $snippet['script'] );
			$service_id = $snippet['service_id'];

			// Skip empty scripts
			if ( empty( $script ) ) {
				continue;
			}

			// Augment the script with Usercentrics attributes
			$augmented = Termageddon_Usercentrics::augment_script_for_usercentrics( $script, $service_id );
			$augmented_scripts[] = $augmented;
		}

		return ! empty( $augmented_scripts ) ? implode( PHP_EOL, $augmented_scripts ) : '';
	}

	/**
	 * Dynamically hide or show the termageddon script based on settings. Outputs directly to script tag.
	 */
	public function build_termageddon_script( $is_enqueue = false ) {

		$geo_auto_accept = false;

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

		// Suppress initially while the hosted geolocation script decides whether
		// consent is required for this visitor.
		if ( Termageddon_Usercentrics::is_geoip_enabled() ) {
			$script .= '<script type="application/javascript">var UC_UI_SUPPRESS_CMP_DISPLAY = true;</script>';
		}

		// Append augmented script snippets
		$augmented_snippets = $this->get_augmented_script_snippets();
		if ( ! empty( $augmented_snippets ) ) {
			$script .= PHP_EOL . $augmented_snippets;
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
			// The handle must always be registered in enqueue mode, otherwise the
			// script_loader_tag filter never fires and no embed code is output at all.
			// The URL is placed here to "play by the rules"... but it doesn't actually do anything:
			// the whole tag is overwritten by the script_loader_tag filter.
			$placeholder_src = Termageddon_Usercentrics::is_auto_blocker_disabled()
				? '//app.usercentrics.eu/browser-ui/latest/loader.js'
				: '//privacy-proxy.usercentrics.eu/latest/uc-block.bundle.js';
			wp_enqueue_script( $this->plugin_name . '-scripts', $placeholder_src, array(), $this->version, false );
		}

		foreach ( Termageddon_Usercentrics::get_integrations() as $integration => $integration_config ) {
			$should_enqueue_script = ! isset( $integration_config['enqueue_script'] ) || $integration_config['enqueue_script'];

			if ( $should_enqueue_script && Termageddon_Usercentrics::is_integration_enabled( $integration, $integration_config['default'] ) ) {
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
			$result = self::build_termageddon_script( true );
			// build_termageddon_script may return null (bare return) when disabled.
			// WordPress expects this filter to return a string; null would cause the
			// original tag to be replaced with nothing (which is correct for disabled
			// state), but we should be explicit about it.
			$tag = ( null !== $result ) ? $result : '';
		}
		if ( $this->should_gate_hubspot_script( $handle ) ) {
			$tag = $this->gate_hubspot_script_tag( $tag );
		}
		if ( $this->should_gate_addtoany_script( $handle ) ) {
			$tag = $this->gate_scripts_for_usercentrics( $tag, 'AddToAny' );
		}
		if ( $this->should_gate_jetpack_script( $handle ) ) {
			$tag = $this->gate_scripts_for_usercentrics( $tag, 'JetPack (WordPress Plugin)' );
		}
		if ( $this->should_gate_meta_for_woocommerce_script( $handle ) ) {
			$tag = $this->gate_meta_for_woocommerce_script_tag( $tag );
		}
		return $tag;
	}

	/**
	 * Determine whether an AddToAny script should be gated for consent.
	 *
	 * AddToAny's remote loader is not consistently recognized by the
	 * Usercentrics Smart Data Protector, so explicitly hand both of the
	 * plugin's frontend script handles to Usercentrics.
	 *
	 * @param string $handle The script handle/ID.
	 * @return bool
	 */
	private function should_gate_addtoany_script( string $handle ): bool {
		if ( is_admin() || ! Termageddon_Usercentrics::is_integration_enabled( 'addtoany' ) ) {
			return false;
		}

		return in_array( $handle, array( 'addtoany-core', 'addtoany-jquery' ), true );
	}

	/**
	 * Determine whether the Jetpack tracking script should be gated for consent.
	 *
	 * Jetpack's Tracks client loads stats.wp.com/w.js using the jp-tracks
	 * handle. Usercentrics does not consistently recognize this loader, so
	 * hand it to the JetPack (WordPress Plugin) service explicitly.
	 *
	 * @param string $handle The script handle/ID.
	 * @return bool
	 */
	private function should_gate_jetpack_script( string $handle ): bool {
		if ( is_admin() || ! Termageddon_Usercentrics::is_integration_enabled( 'jetpack_stats' ) ) {
			return false;
		}

		return in_array( $handle, array( 'jp-tracks' ), true );
	}

	/**
	 * Convert every script tag in a WordPress handle to Usercentrics control.
	 *
	 * WordPress can combine inline before/after scripts and a remote loader in
	 * one filtered HTML string, so every script tag must be gated. Existing
	 * attributes remain unchanged apart from replacing the script type.
	 * data-uc-untouch keeps the Smart Data Protector from re-blocking the tag
	 * under its own src-based service detection.
	 *
	 * @param string $tag                  Script tag HTML.
	 * @param string $usercentrics_service Exact Usercentrics service name.
	 * @return string
	 */
	private function gate_scripts_for_usercentrics( string $tag, string $usercentrics_service ): string {
		$tag = preg_replace( '/\s+type=(["\'])[^"\']*\1/i', '', $tag );
		$usercentrics_service = esc_attr( $usercentrics_service );

		return preg_replace_callback(
			'/<script\b/i',
			static function () use ( $usercentrics_service ) {
				return '<script type="text/plain" data-usercentrics="' . $usercentrics_service . '" data-uc-untouch';
			},
			$tag
		);
	}

	/**
	 * Determine whether the current script should be gated for HubSpot consent.
	 *
	 * @param string $handle The script handle/ID.
	 * @return bool
	 */
	private function should_gate_hubspot_script( string $handle ): bool {
		if ( is_admin() || ! Termageddon_Usercentrics::is_integration_enabled( 'hubspot_plugin' ) ) {
			return false;
		}

		$hubspot_handles = array(
			'leadin-script-loader-js',
			'leadin-forms-v2',
			'leadin-forms-v4',
			'leadin-meeting',
		);

		return in_array( $handle, $hubspot_handles, true );
	}

	/**
	 * Convert a HubSpot script tag to a Usercentrics-controlled script.
	 *
	 * @param string $tag The script tag HTML.
	 * @return string
	 */
	private function gate_hubspot_script_tag( string $tag ): string {
		$usercentrics_service = 'HubSpot';

		if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
			$processor = new WP_HTML_Tag_Processor( $tag );

			if ( $processor->next_tag( 'script' ) ) {
				$processor->set_attribute( 'type', 'text/plain' );
				$processor->set_attribute( 'data-usercentrics', $usercentrics_service );
				$processor->set_attribute( 'data-uc-untouch', true );

				return $processor->get_updated_html();
			}
		}

		$tag = preg_replace( '/\s+type=(["\']).*?\1/i', '', $tag );
		$tag = preg_replace(
			'/^<script\b/i',
			'<script type="text/plain" data-usercentrics="' . esc_attr( $usercentrics_service ) . '" data-uc-untouch',
			$tag,
			1
		);

		return $tag;
	}

	/**
	 * Set Meta for WooCommerce pixel script attributes for Usercentrics compatibility.
	 *
	 * @param array $attrs The existing script attributes.
	 * @return array
	 */
	public function filter_meta_for_woocommerce_pixel_script_attributes( $attrs ) {
		if ( ! Termageddon_Usercentrics::is_integration_enabled( 'meta_for_woocommerce' ) ) {
			return $attrs;
		}

		if ( ! is_array( $attrs ) ) {
			$attrs = array();
		}

		$attrs['type'] = 'text/plain';
		$attrs['data-usercentrics'] = 'Facebook Pixel';

		return $attrs;
	}

	/**
	 * Prevent Meta for WooCommerce from setting _fbp and _fbc before consent.
	 *
	 * @param mixed $held Whether Meta signals are currently held.
	 * @return bool
	 */
	public function hold_meta_for_woocommerce_signals( $held ): bool {
		if ( Termageddon_Usercentrics::is_integration_enabled( 'meta_for_woocommerce' ) ) {
			return true;
		}

		return (bool) $held;
	}

	/**
	 * Release Meta for WooCommerce signals when the Facebook Pixel service is consented to.
	 *
	 * @return void
	 */
	public function release_meta_for_woocommerce_signals() {
		if ( ! Termageddon_Usercentrics::is_integration_enabled( 'meta_for_woocommerce' ) ) {
			return;
		}
		?>
		<script>
		window.addEventListener('ucEvent', function (e) {
			if (!e.detail || e.detail.type !== 'consent_status') return;
			var services = e.detail.services || {};
			var granted = Object.keys(services).some(function (id) {
				var service = services[id];
				return service && service.name === 'Facebook Pixel' && service.status === true;
			});
			if (window.wcFacebookSignals && typeof window.wcFacebookSignals.setState === 'function') {
				window.wcFacebookSignals.setState(granted ? 'active' : 'held');
			}
		});
		</script>
		<?php
	}

	/**
	 * Determine whether a Meta for WooCommerce script should be gated for consent.
	 *
	 * @param string $handle The script handle/ID.
	 * @return bool
	 */
	private function should_gate_meta_for_woocommerce_script( string $handle ): bool {
		if ( is_admin() || ! Termageddon_Usercentrics::is_integration_enabled( 'meta_for_woocommerce' ) ) {
			return false;
		}

		return in_array(
			$handle,
			array(
				'wc-facebook-pixel-events',
				'facebook-for-woocommerce-inline',
				'facebook-capi-param-builder',
				'wc-facebook-signals',
			),
			true
		);
	}

	/**
	 * Convert a Meta for WooCommerce script tag to a Usercentrics-controlled script.
	 *
	 * @param string $tag The script tag HTML.
	 * @return string
	 */
	private function gate_meta_for_woocommerce_script_tag( string $tag ): string {
		$tag = preg_replace( '/\s+type=(["\'])[^"\']*\1/i', '', $tag, 1 );

		return preg_replace(
			'/^<script\b/i',
			'<script type="text/plain" data-usercentrics="Facebook Pixel"',
			$tag,
			1
		);
	}

}
