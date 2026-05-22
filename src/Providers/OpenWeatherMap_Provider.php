<?php
/**
 * OpenWeatherMap Current Weather API 2.5.
 *
 * @package ForWP\Weather
 */

namespace ForWP\Weather\Providers;

use ForWP\Weather\Admin_Settings;
use ForWP\Weather\Contracts\Weather_Credential_Help_Interface;
use ForWP\Weather\Contracts\Weather_Provider_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Live provider using https://api.openweathermap.org/data/2.5/weather
 */
final class OpenWeatherMap_Provider implements Weather_Provider_Interface, Weather_Credential_Help_Interface {

	public const SLUG = 'openweathermap';

	/**
	 * Provider slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return self::SLUG;
	}

	/**
	 * Human-readable provider name.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'OpenWeatherMap', '4wp-weather' );
	}

	/**
	 * Whether this provider is implemented (not a stub).
	 *
	 * @return bool
	 */
	public function is_implemented(): bool {
		return true;
	}

	/**
	 * Whether credentials are configured for this provider.
	 *
	 * @return bool
	 */
	public function is_ready(): bool {
		if ( self::SLUG !== Admin_Settings::instance()->get_credential_provider() ) {
			return false;
		}

		return '' !== Admin_Settings::instance()->get_api_key();
	}

	/**
	 * Fetch current weather from OpenWeatherMap.
	 *
	 * @param float       $latitude        Latitude.
	 * @param float       $longitude       Longitude.
	 * @param string|null $location_query  Optional city query instead of coordinates.
	 * @return array<string,mixed>|\WP_Error
	 */
	public function fetch_current( float $latitude, float $longitude, ?string $location_query = null ) {
		if ( ! $this->is_ready() ) {
			return new \WP_Error(
				'forwp_weather_no_key',
				__( 'OpenWeatherMap API key is not configured.', '4wp-weather' )
			);
		}

		$api_key = Admin_Settings::instance()->get_api_key();

		$query = null !== $location_query ? trim( $location_query ) : '';

		if ( '' !== $query ) {
			$url = add_query_arg(
				array(
					'q'     => $query,
					'appid' => $api_key,
					'units' => 'metric',
				),
				'https://api.openweathermap.org/data/2.5/weather'
			);
		} else {
			$url = add_query_arg(
				array(
					'lat'   => $latitude,
					'lon'   => $longitude,
					'appid' => $api_key,
					'units' => 'metric',
				),
				'https://api.openweathermap.org/data/2.5/weather'
			);
		}

		$response = wp_remote_get(
			esc_url_raw( $url ),
			array(
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new \WP_Error(
				'forwp_weather_transport',
				sprintf(
					/* translators: %s: underlying error message (e.g. SSL, DNS). */
					__( 'Cannot reach OpenWeatherMap: %s', '4wp-weather' ),
					$response->get_error_message()
				),
				array(
					'status' => 0,
				)
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			return new \WP_Error(
				'forwp_weather_bad_response',
				sprintf(
					/* translators: %d: HTTP status code */
					__(
						'OpenWeatherMap returned a non-JSON response (HTTP %d). Check connectivity or SSL.',
						'4wp-weather'
					),
					(int) $code
				),
				array(
					'status' => $code,
				)
			);
		}

		$api_message = isset( $data['message'] ) && is_string( $data['message'] )
			? sanitize_text_field( $data['message'] )
			: '';

		if ( isset( $data['cod'] ) && is_numeric( $data['cod'] ) ) {
			$api_cod = (int) $data['cod'];
			if ( 200 !== $api_cod ) {
				$detail = '' !== $api_message
					? $api_message
					: __( 'Unexpected API response.', '4wp-weather' );

				return new \WP_Error(
					'forwp_weather_upstream',
					sprintf(
						/* translators: 1: OpenWeatherMap cod value, 2: detail text */
						__( 'OpenWeatherMap reported error %1$d: %2$s', '4wp-weather' ),
						$api_cod,
						$detail
					),
					array(
						'status' => $code,
					)
				);
			}
		}

		if ( $code < 200 || $code >= 300 ) {
			$detail = '' !== $api_message
				? $api_message
				: __( 'Unexpected HTTP status.', '4wp-weather' );

			return new \WP_Error(
				'forwp_weather_bad_response',
				sprintf(
					/* translators: 1: HTTP status, 2: detail */
					__( 'OpenWeatherMap HTTP %1$d — %2$s', '4wp-weather' ),
					(int) $code,
					$detail
				),
				array(
					'status' => $code,
				)
			);
		}

		return $this->normalize_payload( $data );
	}

	/**
	 * Path to the block card template for this provider.
	 *
	 * @return string
	 */
	public function get_card_template_path(): string {
		return FORWP_WEATHER_PATH . 'src/weather/templates/card-openweathermap.php';
	}

	/**
	 * Admin help text shown above the API key field.
	 *
	 * @return string
	 */
	public function get_api_key_help_intro(): string {
		return __(
			'Register at OpenWeatherMap and create an API key for “Current weather data”. Paste it here; it is never sent to browsers.',
			'4wp-weather'
		);
	}

	/**
	 * URL to provider API key documentation.
	 *
	 * @return string
	 */
	public function get_api_key_docs_url(): string {
		return 'https://openweathermap.org/appid';
	}

	/**
	 * Link label for API key documentation.
	 *
	 * @return string
	 */
	public function get_api_key_docs_link_label(): string {
		return __( 'Get your API key at OpenWeatherMap', '4wp-weather' );
	}

	/**
	 * Map OWM JSON to canonical plugin shape.
	 *
	 * @param array $data Raw body.
	 * @return array<string,mixed>
	 */
	private function normalize_payload( array $data ): array {
		$weather = isset( $data['weather'][0] ) && is_array( $data['weather'][0] ) ? $data['weather'][0] : array();

		return array(
			'provider'     => self::SLUG,
			'locationName' => isset( $data['name'] ) ? sanitize_text_field( (string) $data['name'] ) : '',
			'country'      => isset( $data['sys']['country'] ) ? sanitize_text_field( (string) $data['sys']['country'] ) : '',
			'latitude'     => isset( $data['coord']['lat'] ) ? (float) $data['coord']['lat'] : null,
			'longitude'    => isset( $data['coord']['lon'] ) ? (float) $data['coord']['lon'] : null,
			'temperature'  => isset( $data['main']['temp'] ) ? (float) $data['main']['temp'] : null,
			'feelsLike'    => isset( $data['main']['feels_like'] ) ? (float) $data['main']['feels_like'] : null,
			'condition'    => isset( $weather['description'] ) ? sanitize_text_field( (string) $weather['description'] ) : '',
			'humidity'     => isset( $data['main']['humidity'] ) ? (int) $data['main']['humidity'] : null,
			'pressure'     => isset( $data['main']['pressure'] ) ? (int) $data['main']['pressure'] : null,
			'windSpeed'    => isset( $data['wind']['speed'] ) ? (float) $data['wind']['speed'] : null,
			'sunrise'      => isset( $data['sys']['sunrise'] ) ? (int) $data['sys']['sunrise'] : null,
			'sunset'       => isset( $data['sys']['sunset'] ) ? (int) $data['sys']['sunset'] : null,
			'fetchedAt'    => time(),
		);
	}
}
