<?php
/**
 * AJAX endpoint for weather JSON.
 *
 * @package Forwp\Weather
 */

namespace Forwp\Weather;

use Forwp\Weather\Providers\OpenWeatherMap_Provider;

defined( 'ABSPATH' ) || exit;

/**
 * Registers AJAX actions for logged-in and guest users.
 */
final class Ajax_Handler {

	private const ACTION = 'forwp_weather';

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'wp_ajax_' . self::ACTION, array( $this, 'handle' ) );
		add_action( 'wp_ajax_nopriv_' . self::ACTION, array( $this, 'handle' ) );
	}

	/**
	 * Handle AJAX request.
	 */
	public function handle(): void {
		check_ajax_referer( self::ACTION, 'nonce' );

		$lat = isset( $_POST['lat'] ) ? sanitize_text_field( wp_unslash( $_POST['lat'] ) ) : '';
		$lon = isset( $_POST['lon'] ) ? sanitize_text_field( wp_unslash( $_POST['lon'] ) ) : '';
		$provider_raw = isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '';
		$provider_slug = '' !== $provider_raw ? sanitize_key( $provider_raw ) : '';

		$location_raw = isset( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : '';
		$location     = trim( $location_raw );

		if ( '' === $provider_slug ) {
			wp_send_json_error(
				array(
					'message' => __( 'Missing weather provider.', '4wp-weather' ),
				),
				400
			);
		}

		$latitude  = is_numeric( $lat ) ? (float) $lat : null;
		$longitude = is_numeric( $lon ) ? (float) $lon : null;

		if ( '' !== $location ) {
			if ( strlen( $location ) > 120 ) {
				wp_send_json_error(
					array(
						'message' => __( 'Location search is too long.', '4wp-weather' ),
					),
					400
				);
			}
			if ( OpenWeatherMap_Provider::SLUG !== $provider_slug ) {
				wp_send_json_error(
					array(
						'message' => __( 'Location search is only available for OpenWeatherMap.', '4wp-weather' ),
					),
					400
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
				wp_send_json_error(
					array(
						'message' => __( 'Invalid coordinates.', '4wp-weather' ),
					),
					400
				);
			}

			if ( $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180 ) {
				wp_send_json_error(
					array(
						'message' => __( 'Coordinates are out of range.', '4wp-weather' ),
					),
					422
				);
			}

			$service = new Weather_Service();
			$result  = $service->get_weather( $latitude, $longitude, $provider_slug, null );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				),
				502
			);
		}

		wp_send_json_success( $result );
	}

	/**
	 * AJAX action name.
	 */
	public static function action(): string {
		return self::ACTION;
	}
}
