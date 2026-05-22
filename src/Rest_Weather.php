<?php
/**
 * Public REST endpoint for front-end weather fetches.
 *
 * @package ForWP\Weather
 */

namespace ForWP\Weather;

use ForWP\Weather\Providers\OpenWeatherMap_Provider;
use WP_REST_Request;
use WP_REST_Response;

defined( 'ABSPATH' ) || exit;

/**
 * GET /forwp-weather/v1/weather — replaces legacy admin-ajax handler.
 */
final class Rest_Weather {

	private const NAMESPACE = 'forwp-weather/v1';

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/weather',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( self::class, 'get_weather' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'lat'      => array(
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'sanitize_text_field',
						),
						'lon'      => array(
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'sanitize_text_field',
						),
						'provider' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_key',
						),
						'location' => array(
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			)
		);
	}

	/**
	 * Fetch normalized weather payload (server-side upstream + cache).
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|\WP_Error
	 */
	public static function get_weather( WP_REST_Request $request ) {
		$lat_raw       = $request->get_param( 'lat' );
		$lon_raw       = $request->get_param( 'lon' );
		$provider_slug = sanitize_key( (string) $request->get_param( 'provider' ) );
		$location      = trim( (string) $request->get_param( 'location' ) );

		if ( '' === $provider_slug ) {
			return new \WP_Error(
				'forwp_weather_missing_provider',
				__( 'Missing weather provider.', '4wp-weather' ),
				array( 'status' => 400 )
			);
		}

		$latitude  = is_numeric( $lat_raw ) ? (float) $lat_raw : null;
		$longitude = is_numeric( $lon_raw ) ? (float) $lon_raw : null;

		if ( '' !== $location ) {
			if ( strlen( $location ) > 120 ) {
				return new \WP_Error(
					'forwp_weather_location_too_long',
					__( 'Location search is too long.', '4wp-weather' ),
					array( 'status' => 400 )
				);
			}
			if ( OpenWeatherMap_Provider::SLUG !== $provider_slug ) {
				return new \WP_Error(
					'forwp_weather_location_provider',
					__( 'Location search is only available for OpenWeatherMap.', '4wp-weather' ),
					array( 'status' => 400 )
				);
			}
			if ( null === $latitude ) {
				$latitude = 0.0;
			}
			if ( null === $longitude ) {
				$longitude = 0.0;
			}
			$service = new Weather_Service();
			$result  = $service->get_weather( $latitude, $longitude, $provider_slug, $location );
		} else {
			if ( null === $latitude || null === $longitude ) {
				return new \WP_Error(
					'forwp_weather_invalid_coordinates',
					__( 'Invalid coordinates.', '4wp-weather' ),
					array( 'status' => 400 )
				);
			}

			if ( $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180 ) {
				return new \WP_Error(
					'forwp_weather_coordinates_range',
					__( 'Coordinates are out of range.', '4wp-weather' ),
					array( 'status' => 422 )
				);
			}

			$service = new Weather_Service();
			$result  = $service->get_weather( $latitude, $longitude, $provider_slug, null );
		}

		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				$result->get_error_code(),
				$result->get_error_message(),
				array( 'status' => 502 )
			);
		}

		return new WP_REST_Response( $result, 200 );
	}
}
