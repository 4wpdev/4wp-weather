<?php
/**
 * Dynamic render template.
 *
 * @package ForWP\Weather
 *
 * @var array         $attributes Block attributes.
 * @var string        $content    Saved markup (unused).
 * @var WP_Block|null $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

use ForWP\Weather\Admin_Settings;
use ForWP\Weather\Provider_Registry;
use ForWP\Weather\Providers\OpenWeatherMap_Provider;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Dynamic block render template; variables are file scope from core include, not globals.
$latitude  = isset( $attributes['latitude'] ) ? (float) $attributes['latitude'] : 0.0;
$longitude = isset( $attributes['longitude'] ) ? (float) $attributes['longitude'] : 0.0;

$provider_slug = isset( $attributes['provider'] ) ? sanitize_key( (string) $attributes['provider'] ) : OpenWeatherMap_Provider::SLUG;
$provider_obj  = Provider_Registry::get( $provider_slug );
if ( null === $provider_obj || ! $provider_obj->is_implemented() ) {
	$provider_slug = OpenWeatherMap_Provider::SLUG;
	$provider_obj  = Provider_Registry::get( $provider_slug );
}

$visibility = array(
	'locationName' => ! empty( $attributes['showLocationName'] ),
	'temperature'  => ! empty( $attributes['showTemperature'] ),
	'feelsLike'    => ! empty( $attributes['showFeelsLike'] ),
	'condition'    => ! empty( $attributes['showCondition'] ),
	'humidity'     => ! empty( $attributes['showHumidity'] ),
	'pressure'     => ! empty( $attributes['showPressure'] ),
	'windSpeed'    => ! empty( $attributes['showWindSpeed'] ),
	'sunrise'      => ! empty( $attributes['showSunrise'] ),
	'sunset'       => ! empty( $attributes['showSunset'] ),
);

$use_browser_geo = ! empty( $attributes['useBrowserGeolocation'] );

$browser_geo_trigger = isset( $attributes['browserGeoTrigger'] ) ? sanitize_key( (string) $attributes['browserGeoTrigger'] ) : 'auto';
if ( 'button' !== $browser_geo_trigger ) {
	$browser_geo_trigger = 'auto';
}

$geo_button_mode = $use_browser_geo && 'button' === $browser_geo_trigger;

$location_change_cooldown_seconds = Admin_Settings::instance()->get_location_change_cooldown_seconds();

/*
 * Stable id for per-block visitor localStorage (saved search query).
 * Same coordinates + provider ⇒ same key (duplicate blocks share storage).
 */
$forwp_instance_key = substr(
	md5(
		$provider_slug . '|' . round( $latitude, 4 ) . '|' . round( $longitude, 4 )
	),
	0,
	24
);

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'data-forwp-weather'         => '1',
		'data-provider'              => $provider_slug,
		'data-lat'                   => esc_attr( (string) round( $latitude, 6 ) ),
		'data-lon'                   => esc_attr( (string) round( $longitude, 6 ) ),
		'data-browser-geo'           => $use_browser_geo ? '1' : '0',
		'data-browser-geo-trigger'   => esc_attr( $browser_geo_trigger ),
		'data-forwp-instance'        => esc_attr( $forwp_instance_key ),
		'data-location-cooldown-sec' => esc_attr( (string) $location_change_cooldown_seconds ),
		'data-visibility'            => esc_attr( wp_json_encode( $visibility ) ),
		'role'                       => 'region',
		'aria-live'                  => 'polite',
		/* Deferred geo: no fetch until the visitor taps the button. */
		'aria-busy'                  => $geo_button_mode ? 'false' : 'true',
		'aria-label'                 => esc_attr__( 'Weather summary', '4wp-weather' ),
	)
);

$labels = array(
	'locationName' => __( 'Location', '4wp-weather' ),
	'temperature'  => __( 'Temperature', '4wp-weather' ),
	'feelsLike'    => __( 'Feels like', '4wp-weather' ),
	'condition'    => __( 'Condition', '4wp-weather' ),
	'humidity'     => __( 'Humidity', '4wp-weather' ),
	'pressure'     => __( 'Pressure', '4wp-weather' ),
	'windSpeed'    => __( 'Wind', '4wp-weather' ),
	'sunrise'      => __( 'Sunrise', '4wp-weather' ),
	'sunset'       => __( 'Sunset', '4wp-weather' ),
);

$card_template = $provider_obj ? $provider_obj->get_card_template_path() : '';

$show_geo_consent_button = $geo_button_mode;

$show_location_search = ! empty( $attributes['showLocationSearch'] );

$forwp_weather_search_field_id = wp_unique_id( 'forwp-weather-search-' );

$forwp_weather_status_id = 'forwp-weather-status-' . preg_replace( '/[^a-z0-9]/i', '', $forwp_instance_key );
$forwp_weather_error_id  = 'forwp-weather-error-' . preg_replace( '/[^a-z0-9]/i', '', $forwp_instance_key );

$forwp_weather_output_json_ld = Admin_Settings::instance()->get_output_json_ld();

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals

?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
<?php
if ( $card_template && is_readable( $card_template ) ) {
	include $card_template;
} else {
	echo '<div class="forwp-weather__card"><p class="forwp-weather__error" role="alert">' . esc_html__( 'Weather template is missing.', '4wp-weather' ) . '</p></div>';
}
?>
</div>
