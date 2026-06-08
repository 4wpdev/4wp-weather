<?php
/**
 * SSR loading shell for weather card (canonical row layout).
 *
 * Used by OpenWeatherMap and as shared skeleton until other providers ship templates.
 *
 * @package ForWP\Weather
 *
 * @var array<string,bool>   $visibility               Field toggles.
 * @var array<string,string> $labels                   Field labels.
 * @var array<string, array{mode: string, icon: string}> $forwp_field_presentation Label modes.
 * @var bool                 $show_geo_consent_button When true, visitor must tap to allow geolocation.
 * @var bool                 $show_location_search    Location search form for visitors.
 * @var string               $forwp_weather_search_field_id Unique id for the search input label.
 * @var string               $forwp_weather_status_id       Unique id for status text (aria-describedby).
 * @var string               $forwp_weather_error_id        Unique id for the error container.
 * @var string               $forwp_instance_key            Block instance key (matches data-forwp-instance).
 * @var bool                 $forwp_weather_output_json_ld  When true, emit JSON-LD stub (filled after REST fetch).
 */

defined( 'ABSPATH' ) || exit;

use ForWP\Weather\Field_Presentation;

if ( empty( $forwp_field_presentation ) || ! is_array( $forwp_field_presentation ) ) {
	$forwp_field_presentation = Field_Presentation::resolve( array() );
}

?>
<div class="forwp-weather__card">
	<p id="<?php echo esc_attr( $forwp_weather_status_id ); ?>" class="forwp-weather__status screen-reader-text">
		<?php
		if ( ! empty( $show_geo_consent_button ) ) {
			esc_html_e(
				'Weather loads after you allow location using the button below.',
				'4wp-weather'
			);
		} else {
			esc_html_e( 'Loading weather…', '4wp-weather' );
		}
		?>
	</p>
	<?php if ( ! empty( $show_geo_consent_button ) ) : ?>
		<div class="forwp-weather__geo-bar">
			<button
				type="button"
				class="forwp-weather__geo-button wp-element-button"
				aria-describedby="<?php echo esc_attr( $forwp_weather_status_id ); ?>"
			>
				<?php esc_html_e( 'Use my location', '4wp-weather' ); ?>
			</button>
		</div>
	<?php endif; ?>
	<?php if ( ! empty( $show_location_search ) ) : ?>
		<form class="forwp-weather__search" role="search" aria-label="<?php esc_attr_e( 'Search weather by place', '4wp-weather' ); ?>">
			<label class="screen-reader-text" for="<?php echo esc_attr( $forwp_weather_search_field_id ); ?>">
				<?php esc_html_e( 'City or place', '4wp-weather' ); ?>
			</label>
			<input
				id="<?php echo esc_attr( $forwp_weather_search_field_id ); ?>"
				class="forwp-weather__search-input"
				type="search"
				name="forwp-weather-location"
				autocomplete="off"
				placeholder="<?php esc_attr_e( 'City or place…', '4wp-weather' ); ?>"
				maxlength="120"
			/>
			<button type="submit" class="forwp-weather__search-submit wp-element-button">
				<?php esc_html_e( 'Search', '4wp-weather' ); ?>
			</button>
		</form>
	<?php endif; ?>
	<?php
		/**
		 * My first favorite HTML tags <table ... <tr> ... <th> ... <td> ...
		 * With lover for old school developer's habits...
		 */
	?>
	<table class="forwp-weather__table">
		<caption class="screen-reader-text">
			<?php esc_html_e( 'Current weather details', '4wp-weather' ); ?>
		</caption>
		<tbody>
		<?php foreach ( $labels as $forwp_weather_field => $forwp_weather_label_text ) : ?>
			<?php if ( empty( $visibility[ $forwp_weather_field ] ) ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<?php
			$forwp_weather_presentation_row = $forwp_field_presentation[ $forwp_weather_field ] ?? array(
				'mode' => Field_Presentation::MODE_TEXT,
				'icon' => 'map-pin',
			);
			$forwp_weather_label_mode       = isset( $forwp_weather_presentation_row['mode'] )
				? sanitize_key( (string) $forwp_weather_presentation_row['mode'] )
				: Field_Presentation::MODE_TEXT;
			$forwp_weather_label_custom     = Field_Presentation::has_custom_label( $forwp_weather_presentation_row );
			?>
			<tr class="forwp-weather__row forwp-weather__row--<?php echo esc_attr( $forwp_weather_field ); ?>" data-forwp-field-row="<?php echo esc_attr( $forwp_weather_field ); ?>">
				<th scope="row" class="forwp-weather__label forwp-weather__label--<?php echo esc_attr( $forwp_weather_label_mode ); ?><?php echo $forwp_weather_label_custom ? ' forwp-weather__label--custom' : ''; ?>">
					<?php
					echo Field_Presentation::render_label_html(
						$forwp_weather_field,
						$forwp_weather_label_text,
						$forwp_weather_presentation_row
					); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in helper.
					?>
				</th>
				<td class="forwp-weather__value" data-forwp-field="<?php echo esc_attr( $forwp_weather_field ); ?>">—</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<p
		id="<?php echo esc_attr( $forwp_weather_error_id ); ?>"
		class="forwp-weather__error"
		role="alert"
		aria-live="assertive"
		aria-relevant="additions text"
		hidden
	></p>
	<?php if ( ! empty( $forwp_weather_output_json_ld ) ) : ?>
		<?php
		/*
		 * Opening <script> must contain a space after the tag name (e.g. <script type=…>) so core
		 * wptexturize() parses the tag as "script" and skips the JSON body (see _wptexturize_pushpop_element).
		 */
		?>
		<script type="application/ld+json" id="forwp-weather-jsonld-<?php echo esc_attr( $forwp_instance_key ); ?>">
		<?php
		echo wp_json_encode(
			array(
				'@context'         => 'https://schema.org',
				'@type'            => 'Observation',
				'name'             => __( 'Current weather', '4wp-weather' ),
				'observationAbout' => array(
					'@type' => 'Place',
					'name'  => __( 'Weather', '4wp-weather' ),
				),
			)
		);
		?>
		</script>
	<?php endif; ?>
</div>
