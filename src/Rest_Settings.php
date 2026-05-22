<?php
/**
 * REST API for the React settings screen.
 *
 * @package ForWP\Weather
 */

namespace ForWP\Weather;

use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

/**
 * Registers routes under `forwp-weather/v1`.
 */
final class Rest_Settings {

	private const NAMESPACE = 'forwp-weather/v1';

	/**
	 * Hook REST route registration.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	/**
	 * Register admin settings and preview routes.
	 *
	 * @return void
	 */
	public static function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/settings',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'get_settings' ),
					'permission_callback' => array( self::class, 'can_manage' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( self::class, 'update_settings' ),
					'permission_callback' => array( self::class, 'can_manage' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/preview',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'get_preview' ),
					'permission_callback' => array( self::class, 'can_manage' ),
					'args'                => array(
						'provider' => array(
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'sanitize_key',
						),
					),
				),
			)
		);
	}

	/**
	 * Whether the current user may manage weather settings.
	 *
	 * @return bool
	 */
	public static function can_manage(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * GET settings payload for the admin UI.
	 */
	public static function get_settings(): WP_REST_Response {
		$stored_key = Admin_Settings::instance()->get_api_key();

		return new WP_REST_Response(
			array(
				'credential_provider'              => Admin_Settings::instance()->get_credential_provider(),
				'api_key_configured'               => '' !== $stored_key,
				'api_key_length'                   => strlen( $stored_key ),
				'preview_latitude'                 => Admin_Settings::instance()->get_preview_latitude_saved(),
				'preview_longitude'                => Admin_Settings::instance()->get_preview_longitude_saved(),
				'location_change_cooldown_seconds' => Admin_Settings::instance()->get_location_change_cooldown_seconds(),
				'show_admin_bar_weather'           => Admin_Settings::instance()->get_show_admin_bar_weather(),
				'output_json_ld'                   => Admin_Settings::instance()->get_output_json_ld(),
				'providers'                        => Provider_Registry::get_admin_status_rows(),
			),
			200
		);
	}

	/**
	 * Live weather sample for the React preview panel (same Weather_Service stack as the block).
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public static function get_preview( WP_REST_Request $request ): WP_REST_Response {
		$slug = $request->get_param( 'provider' );
		$slug = is_string( $slug ) ? sanitize_key( $slug ) : '';

		if ( '' === $slug ) {
			$slug = Admin_Settings::instance()->get_credential_provider();
		}

		$coords  = Admin_Settings::instance()->get_preview_coordinates();
		$service = new Weather_Service();
		$result  = $service->get_weather( $coords['lat'], $coords['lon'], $slug );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response(
				array(
					'weather' => null,
					'query'   => array(
						'latitude'  => $coords['lat'],
						'longitude' => $coords['lon'],
						'provider'  => $slug,
					),
					'error'   => $result->get_error_message(),
					'code'    => $result->get_error_code(),
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'weather' => $result,
				'query'   => array(
					'latitude'  => $coords['lat'],
					'longitude' => $coords['lon'],
					'provider'  => $slug,
				),
				'error'   => null,
				'code'    => null,
			),
			200
		);
	}

	/**
	 * POST updated credential provider and optionally API key.
	 *
	 * @param WP_REST_Request $request Request.
	 */
	public static function update_settings( WP_REST_Request $request ): WP_REST_Response|\WP_Error {
		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			$params = array();
		}

		if ( array_key_exists( 'credential_provider', $params ) ) {
			$slug = sanitize_key( (string) $params['credential_provider'] );
			if ( '' === $slug || ! in_array( $slug, Provider_Registry::implemented_slugs(), true ) ) {
				return new \WP_Error(
					'forwp_weather_invalid_provider',
					__( 'Invalid credential provider.', '4wp-weather' ),
					array( 'status' => 400 )
				);
			}
			update_option( Admin_Settings::CREDENTIAL_PROVIDER_OPTION, $slug, false );
		}

		if ( array_key_exists( 'api_key', $params ) ) {
			$key = $params['api_key'];
			$key = is_string( $key ) ? Admin_Settings::instance()->sanitize_api_key( $key ) : '';
			update_option( Admin_Settings::OPTION_KEY, $key, false );
		}

		self::maybe_update_preview_coordinate(
			$params,
			'preview_latitude',
			Admin_Settings::PREVIEW_LAT_OPTION,
			-90.0,
			90.0
		);
		self::maybe_update_preview_coordinate(
			$params,
			'preview_longitude',
			Admin_Settings::PREVIEW_LON_OPTION,
			-180.0,
			180.0
		);

		if ( array_key_exists( 'location_change_cooldown_seconds', $params ) ) {
			$cd = $params['location_change_cooldown_seconds'];
			$cd = is_numeric( $cd ) ? (int) $cd : 0;
			if ( $cd < 0 ) {
				$cd = 0;
			}
			if ( $cd > (int) DAY_IN_SECONDS ) {
				$cd = (int) DAY_IN_SECONDS;
			}
			update_option( Admin_Settings::LOCATION_CHANGE_COOLDOWN_OPTION, $cd, false );
		}

		if ( array_key_exists( 'show_admin_bar_weather', $params ) ) {
			update_option(
				Admin_Settings::SHOW_ADMIN_BAR_WEATHER_OPTION,
				wp_validate_boolean( $params['show_admin_bar_weather'] ),
				false
			);
		}

		if ( array_key_exists( 'output_json_ld', $params ) ) {
			update_option(
				Admin_Settings::OUTPUT_JSON_LD_OPTION,
				wp_validate_boolean( $params['output_json_ld'] ),
				false
			);
		}

		return self::get_settings();
	}

	/**
	 * Persist optional preview coordinate when present in JSON body.
	 *
	 * @param array<string,mixed> $params Request JSON.
	 * @param string              $param_key Keys such as preview_latitude.
	 * @param string              $option_name Option name.
	 * @param float               $min Minimum inclusive.
	 * @param float               $max Maximum inclusive.
	 */
	private static function maybe_update_preview_coordinate(
		array $params,
		string $param_key,
		string $option_name,
		float $min,
		float $max
	): void {
		if ( ! array_key_exists( $param_key, $params ) ) {
			return;
		}

		$raw = $params[ $param_key ];
		if ( null === $raw || '' === $raw ) {
			delete_option( $option_name );
			return;
		}

		if ( ! is_numeric( $raw ) ) {
			return;
		}

		$value = (float) $raw;
		if ( $value < $min || $value > $max ) {
			return;
		}

		update_option( $option_name, $value, false );
	}
}
