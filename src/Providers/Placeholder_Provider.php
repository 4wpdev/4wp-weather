<?php
/**
 * Roadmap provider stub (no HTTP).
 *
 * @package ForWP\Weather
 */

namespace ForWP\Weather\Providers;

use ForWP\Weather\Contracts\Weather_Provider_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registered for architecture demos; not selectable for live data.
 */
final class Placeholder_Provider implements Weather_Provider_Interface {

	/**
	 * Roadmap provider stub.
	 *
	 * @param string $slug  Stable slug.
	 * @param string $label Admin label.
	 */
	public function __construct(
		private string $slug,
		private string $label
	) {}

	/**
	 * Provider slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Human-readable provider name.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return $this->label;
	}

	/**
	 * Whether this provider is implemented (not a stub).
	 *
	 * @return bool
	 */
	public function is_implemented(): bool {
		return false;
	}

	/**
	 * Whether credentials are configured for this provider.
	 *
	 * @return bool
	 */
	public function is_ready(): bool {
		return false;
	}

	/**
	 * Fetch current weather (stub returns error).
	 *
	 * @param float       $latitude        Latitude.
	 * @param float       $longitude       Longitude.
	 * @param string|null $location_query  Optional city query.
	 * @return \WP_Error
	 */
	public function fetch_current( float $latitude, float $longitude, ?string $location_query = null ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return new \WP_Error(
			'forwp_weather_provider_not_implemented',
			__( 'This weather provider is not implemented yet.', '4wp-weather' )
		);
	}

	/**
	 * Path to the block card template (shared placeholder layout).
	 *
	 * @return string
	 */
	public function get_card_template_path(): string {
		return FORWP_WEATHER_PATH . 'src/weather/templates/card-openweathermap.php';
	}
}
