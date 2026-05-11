<?php
/**
 * Registered weather providers (implemented + roadmap stubs).
 *
 * @package Forwp\Weather
 */

namespace Forwp\Weather;

use Forwp\Weather\Contracts\Weather_Credential_Help_Interface;
use Forwp\Weather\Contracts\Weather_Provider_Interface;
use Forwp\Weather\Providers\OpenWeatherMap_Provider;
use Forwp\Weather\Providers\Placeholder_Provider;

defined( 'ABSPATH' ) || exit;

/**
 * Central registry; extend via filter for addons/tests.
 */
final class Provider_Registry {

	/**
	 * @var array<string, Weather_Provider_Interface>|null
	 */
	private static ?array $providers = null;

	/**
	 * All providers keyed by slug.
	 *
	 * @return array<string, Weather_Provider_Interface>
	 */
	public static function all(): array {
		if ( null !== self::$providers ) {
			return self::$providers;
		}

		$map = array(
			OpenWeatherMap_Provider::SLUG => new OpenWeatherMap_Provider(),
			'tomorrow_io'                 => new Placeholder_Provider(
				'tomorrow_io',
				__( 'Tomorrow.io', '4wp-weather' )
			),
			'visual_crossing'             => new Placeholder_Provider(
				'visual_crossing',
				__( 'Visual Crossing', '4wp-weather' )
			),
			'weatherbit'                  => new Placeholder_Provider(
				'weatherbit',
				__( 'Weatherbit', '4wp-weather' )
			),
			'open_meteo'                  => new Placeholder_Provider(
				'open_meteo',
				__( 'Open-Meteo', '4wp-weather' )
			),
			'accuweather'                 => new Placeholder_Provider(
				'accuweather',
				__( 'AccuWeather', '4wp-weather' )
			),
			'meteosource'                 => new Placeholder_Provider(
				'meteosource',
				__( 'Meteosource', '4wp-weather' )
			),
		);

		/**
		 * Filter full provider map before freeze.
		 *
		 * @param array<string, Weather_Provider_Interface> $map Providers.
		 */
		$filtered        = apply_filters( 'forwp_weather_providers', $map );
		self::$providers = is_array( $filtered ) ? $filtered : $map;

		return self::$providers;
	}

	public static function get( string $slug ): ?Weather_Provider_Interface {
		$slug = sanitize_key( $slug );
		$all  = self::all();

		return isset( $all[ $slug ] ) ? $all[ $slug ] : null;
	}

	/**
	 * Slugs that may be stored on blocks (implemented only).
	 *
	 * @return string[]
	 */
	public static function implemented_slugs(): array {
		$out = array();
		foreach ( self::all() as $slug => $provider ) {
			if ( $provider->is_implemented() ) {
				$out[] = $slug;
			}
		}

		return $out;
	}

	/**
	 * Options for editor SelectControl (disabled = roadmap).
	 *
	 * @return array<int, array{label: string, value: string, disabled: bool}>
	 */
	public static function get_editor_choices(): array {
		$out = array();
		foreach ( self::all() as $provider ) {
			$impl = $provider->is_implemented();
			$out[] = array(
				'label'    => $impl
					? $provider->get_label()
					: sprintf(
						/* translators: %s: provider name */
						__( '%s (planned)', '4wp-weather' ),
						$provider->get_label()
					),
				'value'    => $provider->get_slug(),
				'disabled' => ! $impl,
			);
		}

		return $out;
	}

	/**
	 * Settings screen rows (REST + legacy helpers).
	 *
	 * @return array<int, array{slug: string, label: string, status: string, implemented: bool, api_key_help_intro?: string, api_key_docs_url?: string, api_key_docs_link_label?: string}>
	 */
	public static function get_admin_status_rows(): array {
		$rows      = array();
		$credential = Admin_Settings::instance()->get_credential_provider();

		foreach ( self::all() as $provider ) {
			if ( $provider->is_implemented() ) {
				if ( $credential !== $provider->get_slug() ) {
					$status = __( 'Not used for API credentials', '4wp-weather' );
				} elseif ( $provider->is_ready() ) {
					$status = __( 'Ready', '4wp-weather' );
				} else {
					$status = __( 'Needs API key', '4wp-weather' );
				}
			} else {
				$status = __( 'Planned (stub)', '4wp-weather' );
			}

			$row = array(
				'slug'        => $provider->get_slug(),
				'label'       => $provider->get_label(),
				'status'      => $status,
				'implemented' => $provider->is_implemented(),
			);

			if ( $provider instanceof Weather_Credential_Help_Interface ) {
				$url = esc_url_raw(
					$provider->get_api_key_docs_url(),
					array( 'http', 'https' )
				);
				if ( '' !== $url ) {
					$row['api_key_help_intro']       = $provider->get_api_key_help_intro();
					$row['api_key_docs_url']         = $url;
					$row['api_key_docs_link_label'] = $provider->get_api_key_docs_link_label();
				}
			}

			$rows[] = $row;
		}

		return $rows;
	}
}
