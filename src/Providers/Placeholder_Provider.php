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
	 * @param string $slug  Stable slug.
	 * @param string $label Admin label.
	 */
	public function __construct(
		private string $slug,
		private string $label
	) {}

	public function get_slug(): string {
		return $this->slug;
	}

	public function get_label(): string {
		return $this->label;
	}

	public function is_implemented(): bool {
		return false;
	}

	public function is_ready(): bool {
		return false;
	}

	public function fetch_current( float $latitude, float $longitude, ?string $location_query = null ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return new \WP_Error(
			'forwp_weather_provider_not_implemented',
			__( 'This weather provider is not implemented yet.', '4wp-weather' )
		);
	}

	public function get_card_template_path(): string {
		return FORWP_WEATHER_PATH . 'src/weather/templates/card-openweathermap.php';
	}
}
