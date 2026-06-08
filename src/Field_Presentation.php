<?php
/**
 * Weather field label presentation (text / icon / icon + text / custom icon).
 *
 * @package ForWP\Weather
 */

namespace ForWP\Weather;

defined( 'ABSPATH' ) || exit;

/**
 * Builtin icon registry and per-field presentation resolver.
 */
final class Field_Presentation {

	public const MODE_TEXT        = 'text';
	public const MODE_ICON          = 'icon';
	public const MODE_ICON_TEXT     = 'icon-text';
	public const MODE_CUSTOM_ICON   = 'custom-icon';

	/**
	 * Ordered weather table fields.
	 *
	 * @var string[]
	 */
	public const FIELD_KEYS = array(
		'locationName',
		'temperature',
		'feelsLike',
		'condition',
		'humidity',
		'pressure',
		'windSpeed',
		'sunrise',
		'sunset',
	);

	/**
	 * Default presentation per field.
	 *
	 * @return array<string, array{mode: string, icon: string, iconColor: string, iconBackground: string, iconPadding: string, customSvg: string}>
	 */
	public static function get_defaults(): array {
		$base = array(
			'mode'              => self::MODE_TEXT,
			'icon'              => 'map-pin',
			'labelColor'        => '',
			'iconColor'         => '',
			'iconBackground'    => '',
			'iconPadding'       => '',
			'iconPaddingTop'    => '',
			'iconPaddingRight'  => '',
			'iconPaddingBottom' => '',
			'iconPaddingLeft'   => '',
			'customIconId'      => 0,
			'customSvg'         => '',
			'labelText'         => '',
		);

		return array(
			'locationName' => array_merge( $base, array( 'icon' => 'map-pin' ) ),
			'temperature'  => array_merge( $base, array( 'icon' => 'thermometer' ) ),
			'feelsLike'    => array_merge( $base, array( 'icon' => 'thermometer-sun' ) ),
			'condition'    => array_merge( $base, array( 'icon' => 'cloud-sun' ) ),
			'humidity'     => array_merge( $base, array( 'icon' => 'droplets' ) ),
			'pressure'     => array_merge( $base, array( 'icon' => 'gauge' ) ),
			'windSpeed'    => array_merge( $base, array( 'icon' => 'wind' ) ),
			'sunrise'      => array_merge( $base, array( 'icon' => 'sunrise' ) ),
			'sunset'       => array_merge( $base, array( 'icon' => 'sunset' ) ),
		);
	}

	/**
	 * Builtin icon slug => translated label (editor + docs).
	 *
	 * @return array<string, string>
	 */
	public static function get_icon_catalog(): array {
		return array(
			'map-pin'         => \__( 'Map pin', '4wp-weather' ),
			'thermometer'     => \__( 'Thermometer', '4wp-weather' ),
			'thermometer-sun' => \__( 'Thermometer (sun)', '4wp-weather' ),
			'cloud-sun'       => \__( 'Cloud and sun', '4wp-weather' ),
			'droplets'        => \__( 'Droplets', '4wp-weather' ),
			'gauge'           => \__( 'Gauge', '4wp-weather' ),
			'wind'            => \__( 'Wind', '4wp-weather' ),
			'sunrise'         => \__( 'Sunrise', '4wp-weather' ),
			'sunset'          => \__( 'Sunset', '4wp-weather' ),
		);
	}

	/**
	 * Merge block attributes with defaults.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return array<string, array{mode: string, icon: string, iconColor: string, iconBackground: string, iconPadding: string, customSvg: string}>
	 */
	public static function resolve( array $attributes ): array {
		$defaults = self::get_defaults();
		$raw      = isset( $attributes['fieldPresentation'] ) && is_array( $attributes['fieldPresentation'] )
			? $attributes['fieldPresentation']
			: array();

		$resolved = array();

		foreach ( self::FIELD_KEYS as $field_key ) {
			$base = $defaults[ $field_key ] ?? self::get_defaults()['locationName'];
			$row  = isset( $raw[ $field_key ] ) && is_array( $raw[ $field_key ] )
				? $raw[ $field_key ]
				: array();

			$mode = isset( $row['mode'] ) ? sanitize_key( (string) $row['mode'] ) : $base['mode'];
			if ( ! in_array(
				$mode,
				array(
					self::MODE_TEXT,
					self::MODE_ICON,
					self::MODE_ICON_TEXT,
					self::MODE_CUSTOM_ICON,
				),
				true
			) ) {
				$mode = self::MODE_TEXT;
			}

			$icon = isset( $row['icon'] ) ? sanitize_key( (string) $row['icon'] ) : $base['icon'];
			if ( ! array_key_exists( $icon, self::get_icon_catalog() ) ) {
				$icon = $base['icon'];
			}

			$resolved[ $field_key ] = array(
				'mode'              => $mode,
				'icon'              => $icon,
				'labelColor'        => self::sanitize_css_color(
					isset( $row['labelColor'] ) ? (string) $row['labelColor'] : ''
				),
				'iconColor'         => self::sanitize_css_color(
					isset( $row['iconColor'] ) ? (string) $row['iconColor'] : ''
				),
				'iconBackground'    => self::sanitize_css_color(
					isset( $row['iconBackground'] ) ? (string) $row['iconBackground'] : ''
				),
				'iconPadding'       => self::sanitize_css_spacing(
					isset( $row['iconPadding'] ) ? (string) $row['iconPadding'] : ''
				),
				'iconPaddingTop'    => self::sanitize_css_spacing(
					isset( $row['iconPaddingTop'] ) ? (string) $row['iconPaddingTop'] : ''
				),
				'iconPaddingRight'  => self::sanitize_css_spacing(
					isset( $row['iconPaddingRight'] ) ? (string) $row['iconPaddingRight'] : ''
				),
				'iconPaddingBottom' => self::sanitize_css_spacing(
					isset( $row['iconPaddingBottom'] ) ? (string) $row['iconPaddingBottom'] : ''
				),
				'iconPaddingLeft'   => self::sanitize_css_spacing(
					isset( $row['iconPaddingLeft'] ) ? (string) $row['iconPaddingLeft'] : ''
				),
				'customIconId'      => isset( $row['customIconId'] )
					? absint( $row['customIconId'] )
					: 0,
				'customSvg'         => self::sanitize_custom_svg(
					isset( $row['customSvg'] ) ? (string) $row['customSvg'] : ''
				),
				'labelText'         => self::sanitize_label_text(
					isset( $row['labelText'] ) ? (string) $row['labelText'] : ''
				),
			);
		}

		/**
		 * Filter resolved field presentation per block.
		 *
		 * @param array<string, array{mode: string, icon: string, iconColor: string, iconBackground: string, iconPadding: string, customSvg: string}> $resolved   Resolved rows.
		 * @param array<string, mixed>                                                                                                               $attributes Block attributes.
		 */
		return (array) apply_filters( 'forwp_weather_field_presentation', $resolved, $attributes );
	}

	/**
	 * Effective label for display (custom override or fallback).
	 *
	 * @param string                $fallback Default translated label.
	 * @param array<string, string> $row      Presentation row.
	 * @return string
	 */
	public static function get_effective_label( string $fallback, array $row ): string {
		$custom = isset( $row['labelText'] ) ? trim( (string) $row['labelText'] ) : '';

		return '' !== $custom ? $custom : $fallback;
	}

	/**
	 * Whether the field uses a custom label override.
	 *
	 * @param array<string, string> $row Presentation row.
	 * @return bool
	 */
	public static function has_custom_label( array $row ): bool {
		return isset( $row['labelText'] ) && '' !== trim( (string) $row['labelText'] );
	}

	/**
	 * Render label cell inner HTML for a weather row.
	 *
	 * @param string                            $field_key Field key.
	 * @param string                            $label     Translated label text.
	 * @param array<string, string>             $row       Presentation row.
	 * @return string Safe HTML.
	 */
	public static function render_label_html( string $field_key, string $label, array $row ): string {
		unset( $field_key );

		$mode          = $row['mode'] ?? self::MODE_TEXT;
		$display_label = self::get_effective_label( $label, $row );
		$custom_class  = self::has_custom_label( $row ) ? ' forwp-weather__label-inner--custom' : '';

		$inner_style = self::build_label_inner_style_attr( $row );
		$text_style  = self::build_text_style_attr( $row );

		if ( self::MODE_TEXT === $mode ) {
			return '<span class="forwp-weather__label-inner forwp-weather__label-inner--text' . $custom_class . '"'
				. $inner_style
				. $text_style
				. '>' . esc_html( $display_label ) . '</span>';
		}

		$icon_html = self::render_icon_html( $row );

		if ( self::MODE_ICON === $mode || self::MODE_CUSTOM_ICON === $mode ) {
			return '<span class="forwp-weather__label-inner forwp-weather__label-inner--' . esc_attr( $mode ) . '"'
				. $inner_style
				. '>'
				. $icon_html
				. '<span class="screen-reader-text">' . esc_html( $display_label ) . '</span>'
				. '</span>';
		}

		$text_custom_class = self::has_custom_label( $row ) ? ' forwp-weather__label-text--custom' : '';

		return '<span class="forwp-weather__label-inner forwp-weather__label-inner--icon-text"'
			. $inner_style
			. '>'
			. $icon_html
			. '<span class="forwp-weather__label-text' . $text_custom_class . '"'
			. $text_style
			. '>' . esc_html( $display_label ) . '</span>'
			. '</span>';
	}

	/**
	 * @param array<string, string> $row Presentation row.
	 * @return string Safe HTML.
	 */
	public static function render_icon_html( array $row ): string {
		$mode = $row['mode'] ?? self::MODE_TEXT;
		$slug = $row['icon'] ?? 'map-pin';
		$svg  = '';

		if ( self::MODE_CUSTOM_ICON === $mode ) {
			$svg = self::get_custom_svg_markup( $row );
		} else {
			$svg = self::get_icon_svg( $slug );
		}

		if ( '' === $svg ) {
			return '';
		}

		$style = self::build_icon_style_attr( $row );

		return '<span class="forwp-weather__field-icon" aria-hidden="true"' . $style . '>'
			. \wp_kses( $svg, self::svg_allowed_html() )
			. '</span>';
	}

	/**
	 * @param array<string, string> $row Presentation row.
	 * @return string Attribute fragment or empty.
	 */
	/**
	 * @param array<string, string> $row Presentation row.
	 * @return string Attribute fragment or empty.
	 */
	private static function build_label_inner_style_attr( array $row ): string {
		$styles = array();

		if ( ! empty( $row['iconBackground'] ) ) {
			$styles[] = 'background-color:' . $row['iconBackground'];
		}

		if ( empty( $styles ) ) {
			return '';
		}

		return ' style="' . esc_attr( implode( ';', $styles ) ) . '"';
	}

	/**
	 * @param array<string, string> $row Presentation row.
	 * @return string Attribute fragment or empty.
	 */
	private static function build_text_style_attr( array $row ): string {
		if ( empty( $row['labelColor'] ) ) {
			return '';
		}

		return ' style="' . esc_attr( 'color:' . $row['labelColor'] ) . '"';
	}

	/**
	 * @param array<string, string> $row Presentation row.
	 * @return string Attribute fragment or empty.
	 */
	private static function build_icon_style_attr( array $row ): string {
		$styles = array();

		if ( ! empty( $row['iconColor'] ) ) {
			$styles[] = 'color:' . $row['iconColor'];
		}

		$padding_sides = array(
			'padding-top'    => $row['iconPaddingTop'] ?? '',
			'padding-right'  => $row['iconPaddingRight'] ?? '',
			'padding-bottom' => $row['iconPaddingBottom'] ?? '',
			'padding-left'   => $row['iconPaddingLeft'] ?? '',
		);

		foreach ( $padding_sides as $property => $value ) {
			if ( ! empty( $value ) ) {
				$styles[] = $property . ':' . $value;
			}
		}

		if (
			empty( $row['iconPaddingTop'] ?? '' )
			&& empty( $row['iconPaddingRight'] ?? '' )
			&& empty( $row['iconPaddingBottom'] ?? '' )
			&& empty( $row['iconPaddingLeft'] ?? '' )
			&& ! empty( $row['iconPadding'] )
		) {
			$styles[] = 'padding:' . $row['iconPadding'];
		}

		if ( empty( $styles ) ) {
			return '';
		}

		return ' style="' . esc_attr( implode( ';', $styles ) ) . '"';
	}

	/**
	 * @param string $color Raw color value.
	 * @return string
	 */
	private static function sanitize_css_color( string $color ): string {
		$color = trim( sanitize_text_field( $color ) );
		if ( '' === $color ) {
			return '';
		}

		$hex = sanitize_hex_color( $color );
		if ( $hex ) {
			return $hex;
		}

		if ( preg_match( '/^var\(--wp--preset--color--[a-z0-9-]+\)$/', $color ) ) {
			return $color;
		}

		return '';
	}

	/**
	 * @param string $value Raw spacing value.
	 * @return string
	 */
	private static function sanitize_css_spacing( string $value ): string {
		$value = trim( sanitize_text_field( $value ) );
		if ( preg_match( '/^\d+(\.\d+)?(px|em|rem|%)$/', $value ) ) {
			return $value;
		}

		return '';
	}

	/**
	 * @param array<string, mixed> $row Presentation row.
	 * @return string Safe inline SVG or empty.
	 */
	private static function get_custom_svg_markup( array $row ): string {
		$attachment_id = isset( $row['customIconId'] ) ? absint( $row['customIconId'] ) : 0;

		if ( $attachment_id > 0 ) {
			$loaded = self::load_svg_from_attachment( $attachment_id );
			if ( '' !== $loaded ) {
				return $loaded;
			}
		}

		return isset( $row['customSvg'] ) ? (string) $row['customSvg'] : '';
	}

	/**
	 * @param int $attachment_id Media attachment ID.
	 * @return string
	 */
	private static function load_svg_from_attachment( int $attachment_id ): string {
		if ( 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
			return '';
		}

		$path = get_attached_file( $attachment_id );
		if ( is_string( $path ) && is_readable( $path ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local attachment file.
			$raw = file_get_contents( $path );

			return self::sanitize_custom_svg( is_string( $raw ) ? $raw : '' );
		}

		$url = wp_get_attachment_url( $attachment_id );
		if ( ! $url ) {
			return '';
		}

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			return '';
		}

		return self::sanitize_custom_svg( (string) wp_remote_retrieve_body( $response ) );
	}

	/**
	 * @param string $svg Raw SVG markup.
	 * @return string
	 */
	private static function sanitize_custom_svg( string $svg ): string {
		$svg = trim( $svg );
		if ( '' === $svg ) {
			return '';
		}

		return \wp_kses( $svg, self::svg_allowed_html() );
	}

	/**
	 * @param string $label Raw custom label text.
	 * @return string
	 */
	private static function sanitize_label_text( string $label ): string {
		return trim( \sanitize_text_field( $label ) );
	}

	/**
	 * @return array<string, array<string, bool>>
	 */
	private static function svg_allowed_html(): array {
		return array(
			'svg'    => array(
				'xmlns'           => true,
				'viewbox'         => true,
				'width'           => true,
				'height'          => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'aria-hidden'     => true,
				'focusable'       => true,
				'role'            => true,
			),
			'path'   => array(
				'd'               => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
			),
			'circle' => array(
				'cx'           => true,
				'cy'           => true,
				'r'            => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'line'   => array(
				'x1'             => true,
				'y1'             => true,
				'x2'             => true,
				'y2'             => true,
				'stroke'         => true,
				'stroke-width'   => true,
				'stroke-linecap' => true,
			),
		);
	}

	/**
	 * Inline SVG markup (currentColor strokes).
	 *
	 * @param string $slug Icon slug.
	 * @return string SVG string or empty.
	 */
	public static function get_icon_svg( string $slug ): string {
		$icons = array(
			'map-pin'         => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 21s6-5.2 6-10a6 6 0 1 0-12 0c0 4.8 6 10 6 10Z"/><circle cx="12" cy="11" r="2.5"/></svg>',
			'thermometer'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14 14.8V5a2 2 0 1 0-4 0v9.8a4 4 0 1 0 4 0Z"/><line x1="10" y1="9" x2="10" y2="15"/></svg>',
			'thermometer-sun' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14 14.8V5a2 2 0 1 0-4 0v9.8a4 4 0 1 0 4 0Z"/><circle cx="18" cy="6" r="2"/><line x1="18" y1="2" x2="18" y2="3"/><line x1="22" y1="6" x2="21" y2="6"/><line x1="20.2" y1="3.8" x2="19.5" y2="4.5"/></svg>',
			'cloud-sun'       => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3v2"/><path d="m4.9 4.9 1.4 1.4"/><path d="M20 12h2"/><path d="m18.4 6.3-1.4 1.4"/><circle cx="12" cy="7" r="3"/><path d="M7 18a4 4 0 0 1 0-8 4.8 4.8 0 0 1 9.2 1.5A3.5 3.5 0 0 1 18.5 18Z"/></svg>',
			'droplets'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 22a6 6 0 0 0 6-10c0-4-6-10-6-10S6 8 6 12a6 6 0 0 0 6 10Z"/></svg>',
			'gauge'           => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m12 14 4-6"/><path d="M12 6v2"/><circle cx="12" cy="14" r="8"/></svg>',
			'wind'            => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2"/><path d="M9.6 4.6A2 2 0 1 1 11 8H2"/><path d="M12.6 19.4A2 2 0 1 0 14 16H2"/></svg>',
			'sunrise'         => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 2v4"/><path d="m4.9 10.9 2.8 2.8"/><path d="M2 18h2"/><path d="M20 18h2"/><path d="m16.3 13.7 2.8-2.8"/><path d="M12 10a4 4 0 1 0 0 8 7 7 0 0 1 0-8Z"/><path d="M4 22h16"/></svg>',
			'sunset'          => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 10V2"/><path d="m4.9 10.9 2.8 2.8"/><path d="M2 18h2"/><path d="M20 18h2"/><path d="m16.3 13.7 2.8-2.8"/><path d="M12 14a4 4 0 1 0 0 8 7 7 0 0 1 0-8Z"/><path d="M4 22h16"/></svg>',
		);

		return $icons[ $slug ] ?? '';
	}
}
