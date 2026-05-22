<?php
/**
 * Weather data provider contract.
 *
 * @package ForWP\Weather
 */

namespace ForWP\Weather\Contracts;

defined( 'ABSPATH' ) || exit;

/**
 * One provider = one upstream API shape + normalization to the plugin canonical payload.
 */
interface Weather_Provider_Interface {

	/**
	 * Stable slug (block attribute, cache segment).
	 */
	public function get_slug(): string;

	/**
	 * Human-readable name (admin / editor).
	 */
	public function get_label(): string;

	/**
	 * False for roadmap stubs (no integration shipped).
	 */
	public function is_implemented(): bool;

	/**
	 * True when live requests may run (e.g. API key saved if required).
	 */
	public function is_ready(): bool;

	/**
	 * Fetch and normalize current conditions.
	 *
	 * @param float       $latitude        Latitude (used when $location_query is empty).
	 * @param float       $longitude       Longitude (used when $location_query is empty).
	 * @param string|null $location_query  Non-empty place name for providers that support it (e.g. OpenWeatherMap `q`).
	 * @return array|\WP_Error Canonical payload per plugin docs or error.
	 */
	public function fetch_current( float $latitude, float $longitude, ?string $location_query = null );

	/**
	 * PHP partial for SSR card markup (loading shell).
	 *
	 * @return string Absolute path.
	 */
	public function get_card_template_path(): string;
}
