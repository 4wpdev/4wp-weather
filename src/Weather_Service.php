<?php
/**
 * Cached weather retrieval delegated to providers.
 *
 * @package ForWP\Weather
 */

namespace ForWP\Weather;

use ForWP\Weather\Providers\OpenWeatherMap_Provider;

defined( 'ABSPATH' ) || exit;

/**
 * Orchestrates provider fetch + transients.
 */
final class Weather_Service {

	private const CACHE_TTL = HOUR_IN_SECONDS;

	private const TRANSIENT_PREFIX = 'forwp_w_';

	private const REGISTRY_OPTION = 'forwp_weather_cache_keys';

	/**
	 * Retrieve structured weather data for coordinates (cached).
	 *
	 * @param float       $latitude        Latitude (ignored when $location_query is non-empty).
	 * @param float       $longitude       Longitude (ignored when $location_query is non-empty).
	 * @param string      $provider_slug   Provider slug from block / request.
	 * @param string|null $location_query  Trimmed place name; empty string means use coordinates.
	 * @return array|\WP_Error Normalized payload or error.
	 */
	public function get_weather( float $latitude, float $longitude, string $provider_slug = OpenWeatherMap_Provider::SLUG, ?string $location_query = null ) {
		$provider_slug = sanitize_key( $provider_slug );
		$provider      = Provider_Registry::get( $provider_slug );

		if ( null === $provider ) {
			return new \WP_Error(
				'forwp_weather_unknown_provider',
				__( 'Unknown weather provider.', '4wp-weather' )
			);
		}

		if ( ! $provider->is_implemented() ) {
			return new \WP_Error(
				'forwp_weather_provider_not_implemented',
				__( 'This weather provider is not implemented yet.', '4wp-weather' )
			);
		}

		if ( ! $provider->is_ready() ) {
			return new \WP_Error(
				'forwp_weather_not_configured',
				__( 'Weather provider is not configured.', '4wp-weather' )
			);
		}

		$query = null !== $location_query ? trim( $location_query ) : '';
		$query = '' !== $query ? $query : null;

		$key    = $this->build_cache_key( $provider_slug, $latitude, $longitude, $query );
		$cached = get_transient( $key );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$result = $provider->fetch_current( $latitude, $longitude, $query );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! is_array( $result ) ) {
			return new \WP_Error(
				'forwp_weather_bad_response',
				__( 'Unable to load weather data.', '4wp-weather' )
			);
		}

		set_transient( $key, $result, self::CACHE_TTL );
		$this->remember_cache_key( $key );

		return $result;
	}

	/**
	 * Build transient name for provider + coordinates.
	 *
	 * @param string      $provider_slug   Provider slug.
	 * @param float       $latitude        Latitude.
	 * @param float       $longitude       Longitude.
	 * @param string|null $location_query  Non-null non-empty string keys cache by place name.
	 */
	private function build_cache_key( string $provider_slug, float $latitude, float $longitude, ?string $location_query = null ): string {
		if ( null !== $location_query && '' !== $location_query ) {
			$fingerprint = wp_json_encode(
				array(
					'p' => $provider_slug,
					'q' => strtolower( $location_query ),
				)
			);
		} else {
			$fingerprint = wp_json_encode(
				array(
					'p'   => $provider_slug,
					'lat' => round( $latitude, 4 ),
					'lon' => round( $longitude, 4 ),
				)
			);
		}

		return self::TRANSIENT_PREFIX . md5( (string) $fingerprint );
	}

	/**
	 * Track transient keys for CLI flush.
	 *
	 * @param string $transient_key Transient key.
	 */
	private function remember_cache_key( string $transient_key ): void {
		$keys   = get_option( self::REGISTRY_OPTION, array() );
		$keys   = is_array( $keys ) ? $keys : array();
		$keys[] = $transient_key;
		$keys   = array_values( array_unique( array_filter( $keys ) ) );
		update_option( self::REGISTRY_OPTION, $keys, false );
	}

	/**
	 * Delete all plugin weather transients.
	 *
	 * @return int Number of deleted transients.
	 */
	public static function flush_all_caches(): int {
		$keys = get_option( self::REGISTRY_OPTION, array() );
		$keys = is_array( $keys ) ? $keys : array();
		$count = 0;
		foreach ( $keys as $key ) {
			if ( is_string( $key ) && '' !== $key ) {
				delete_transient( $key );
				++$count;
			}
		}
		delete_option( self::REGISTRY_OPTION );

		return $count;
	}
}
