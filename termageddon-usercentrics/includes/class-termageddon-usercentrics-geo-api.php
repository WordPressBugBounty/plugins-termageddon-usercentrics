<?php
/**
 * Geolocation API integration for Termageddon Usercentrics.
 *
 * Encapsulates the hosted-API path that replaces the on-device MaxMind database.
 * When enabled, the visitor's browser fetches `https://geo.termageddon.com` and
 * caches the result client-side; the show/hide decision is made entirely in JS.
 *
 * @link       https://termageddon.com
 * @since      1.10.0
 *
 * @package    Termageddon_Usercentrics
 * @subpackage Termageddon_Usercentrics/includes
 */

/**
 * Geo-API helper.
 *
 * @since      1.10.0
 * @package    Termageddon_Usercentrics
 * @subpackage Termageddon_Usercentrics/includes
 * @author     Termageddon <support@termageddon.com>
 */
class Termageddon_Usercentrics_Geo_Api {

	/**
	 * The hosted geolocation API URL.
	 *
	 * @var string
	 */
	public const API_URL = 'https://geo.termageddon.com';

	/**
	 * The default API key sent in the `X-Tm-Key` header.
	 *
	 * Filterable via `termageddon_geo_api_key` for rotation/override.
	 *
	 * @var string
	 */
	public const API_KEY = 'tm_geo_v1_X6GaXa6nRXwE026';

	/**
	 * Mapping from internal location key to ISO-3166-2 region (US state) code.
	 *
	 * @var array<string,string>
	 */
	public const GEOLOCATION_KEY_TO_REGION_CODE = array(
		'california'  => 'CA',
		'colorado'    => 'CO',
		'connecticut' => 'CT',
		'delaware'    => 'DE',
		'indiana'     => 'IN',
		'oregon'      => 'OR',
		'texas'       => 'TX',
		'utah'        => 'UT',
		'virginia'    => 'VA',
	);

	/**
	 * ISO-3166-1 alpha-2 country codes that fall under EU/EEA enforcement (no UK).
	 *
	 * @var array<int,string>
	 */
	public const EU_COUNTRY_CODES = array(
		'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
		'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
		'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
		// EEA non-EU.
		'NO', 'IS', 'LI',
	);

	/**
	 * Debug-override key → mock geo payload, used when `?termageddon-usercentrics-debug=<key>` is set.
	 *
	 * Mirrors the IP-based test fixtures in the legacy MaxMind path.
	 *
	 * @var array<string,array{country:string,region_code:?string,city:?string}>
	 */
	public const DEBUG_FIXTURES = array(
		'colorado'   => array(
			'country'     => 'US',
			'region_code' => 'CO',
			'city'        => 'Denver',
		),
		'california' => array(
			'country'     => 'US',
			'region_code' => 'CA',
			'city'        => 'Los Angeles',
		),
		'canada'     => array(
			'country'     => 'CA',
			'region_code' => null,
			'city'        => 'Toronto',
		),
		'denmark'    => array(
			'country'     => 'DK',
			'region_code' => null,
			'city'        => 'Copenhagen',
		),
		'england'    => array(
			'country'     => 'GB',
			'region_code' => 'ENG',
			'city'        => 'London',
		),
		'wales'      => array(
			'country'     => 'GB',
			'region_code' => 'WLS',
			'city'        => 'Cardiff',
		),
		'france'     => array(
			'country'     => 'FR',
			'region_code' => null,
			'city'        => 'Paris',
		),
		'eu'         => array(
			'country'     => 'DE',
			'region_code' => null,
			'city'        => 'Berlin',
		),
		'uk'         => array(
			'country'     => 'GB',
			'region_code' => 'ENG',
			'city'        => 'London',
		),
	);

	/**
	 * Is the new geolocation service opted into?
	 *
	 * @return bool
	 */
	public static function is_enabled(): bool {
		return (bool) get_option( 'termageddon_use_geo_api', false );
	}

	/**
	 * Resolve the API key, allowing override via filter.
	 *
	 * @return string
	 */
	public static function get_api_key(): string {
		return (string) apply_filters( 'termageddon_geo_api_key', self::API_KEY );
	}

	/**
	 * Build the payload that gets localized to JS as part of `termageddon_usercentrics_obj`.
	 *
	 * Returns the per-location matching rules + enabled flag from existing options, so the
	 * client-side decision logic can mirror the PHP `should_hide_due_to_location` semantics
	 * without round-tripping to admin-ajax.
	 *
	 * @return array
	 */
	public static function build_localization_payload(): array {
		$locations = array();
		foreach ( Termageddon_Usercentrics::get_geolocation_locations() as $loc_key => $loc ) {
			$rule = self::build_location_rule( $loc_key );
			if ( null === $rule ) {
				continue;
			}
			$locations[ $loc_key ] = array_merge(
				$rule,
				array(
					'enabled' => Termageddon_Usercentrics::is_geoip_location_enabled_in( $loc_key ),
				)
			);
		}

		$payload = array(
			'use_geo_api'  => 'true',
			'geo_api_url'  => self::API_URL,
			'geo_api_key'  => self::get_api_key(),
			'geo_locations' => $locations,
		);

		$debug_key = (string) get_query_var( 'termageddon-usercentrics-debug' );
		if ( '' !== $debug_key && isset( self::DEBUG_FIXTURES[ $debug_key ] ) ) {
			$payload['geo_debug_override'] = self::DEBUG_FIXTURES[ $debug_key ];
		}

		return $payload;
	}

	/**
	 * Build the matching rule for a single location key.
	 *
	 * @param string $loc_key Internal location key (e.g. "eu", "uk", "california").
	 * @return array|null Rule definition, or null if the key has no rule.
	 */
	private static function build_location_rule( string $loc_key ): ?array {
		switch ( $loc_key ) {
			case 'eu':
				return array(
					'type'   => 'country_in_list',
					'codes'  => self::EU_COUNTRY_CODES,
				);
			case 'uk':
				return array(
					'type' => 'country',
					'code' => 'GB',
				);
			case 'canada':
				return array(
					'type' => 'country',
					'code' => 'CA',
				);
			default:
				if ( isset( self::GEOLOCATION_KEY_TO_REGION_CODE[ $loc_key ] ) ) {
					return array(
						'type'        => 'us_state',
						'region_code' => self::GEOLOCATION_KEY_TO_REGION_CODE[ $loc_key ],
					);
				}
				return null;
		}
	}
}
