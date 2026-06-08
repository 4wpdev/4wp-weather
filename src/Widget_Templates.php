<?php
/**
 * Widget layout + style presets for new blocks and site defaults.
 *
 * @package ForWP\Weather
 */

namespace ForWP\Weather;

defined( 'ABSPATH' ) || exit;

/**
 * Small / Compact / Advanced layouts, each with Dark or White style.
 */
final class Widget_Templates {

	public const LAYOUT_SMALL    = 'small';

	public const LAYOUT_COMPACT  = 'compact';

	public const LAYOUT_ADVANCED = 'advanced';

	public const STYLE_DARK  = 'dark';

	public const STYLE_WHITE = 'white';

	public const OPTION_LAYOUT = 'forwp_weather_default_widget_layout';

	public const OPTION_STYLE  = 'forwp_weather_default_widget_style';

	/**
	 * Default layout (#3 Advanced).
	 */
	public const DEFAULT_LAYOUT = self::LAYOUT_ADVANCED;

	/**
	 * Default style until design samples ship.
	 */
	public const DEFAULT_STYLE = self::STYLE_DARK;

	/**
	 * @return string[]
	 */
	public static function layout_slugs(): array {
		return array(
			self::LAYOUT_SMALL,
			self::LAYOUT_COMPACT,
			self::LAYOUT_ADVANCED,
		);
	}

	/**
	 * @return string[]
	 */
	public static function style_slugs(): array {
		return array(
			self::STYLE_DARK,
			self::STYLE_WHITE,
		);
	}

	/**
	 * Admin + editor catalog rows.
	 *
	 * @return array<int, array{slug: string, label: string, description: string}>
	 */
	public static function get_layout_catalog(): array {
		return array(
			array(
				'slug'        => self::LAYOUT_SMALL,
				'label'       => \__( 'Small', '4wp-weather' ),
				'description' => \__( 'Location and temperature only — minimal footprint.', '4wp-weather' ),
			),
			array(
				'slug'        => self::LAYOUT_COMPACT,
				'label'       => \__( 'Compact', '4wp-weather' ),
				'description' => \__( 'Core conditions: location, temperature, condition, humidity, and wind.', '4wp-weather' ),
			),
			array(
				'slug'        => self::LAYOUT_ADVANCED,
				'label'       => \__( 'Advanced', '4wp-weather' ),
				'description' => \__( 'All nine parameters with icon labels — full weather card.', '4wp-weather' ),
			),
		);
	}

	/**
	 * @return array<int, array{slug: string, label: string}>
	 */
	public static function get_style_catalog(): array {
		return array(
			array(
				'slug'  => self::STYLE_DARK,
				'label' => \__( 'Dark', '4wp-weather' ),
			),
			array(
				'slug'  => self::STYLE_WHITE,
				'label' => \__( 'White', '4wp-weather' ),
			),
		);
	}

	/**
	 * Saved site default layout.
	 */
	public static function get_site_default_layout(): string {
		$raw = get_option( self::OPTION_LAYOUT, self::DEFAULT_LAYOUT );
		$raw = is_string( $raw ) ? \sanitize_key( $raw ) : self::DEFAULT_LAYOUT;

		return in_array( $raw, self::layout_slugs(), true ) ? $raw : self::DEFAULT_LAYOUT;
	}

	/**
	 * Saved site default style.
	 */
	public static function get_site_default_style(): string {
		$raw = get_option( self::OPTION_STYLE, self::DEFAULT_STYLE );
		$raw = is_string( $raw ) ? \sanitize_key( $raw ) : self::DEFAULT_STYLE;

		return in_array( $raw, self::style_slugs(), true ) ? $raw : self::DEFAULT_STYLE;
	}

	/**
	 * Persist site defaults (used by admin Settings tab).
	 *
	 * @param string $layout Layout slug.
	 * @param string $style  Style slug.
	 */
	public static function save_site_defaults( string $layout, string $style ): void {
		$layout = \sanitize_key( $layout );
		$style  = \sanitize_key( $style );

		if ( ! in_array( $layout, self::layout_slugs(), true ) ) {
			$layout = self::DEFAULT_LAYOUT;
		}
		if ( ! in_array( $style, self::style_slugs(), true ) ) {
			$style = self::DEFAULT_STYLE;
		}

		update_option( self::OPTION_LAYOUT, $layout, false );
		update_option( self::OPTION_STYLE, $style, false );
	}

	/**
	 * Seed Advanced + Dark on first activation.
	 */
	public static function maybe_seed_site_defaults(): void {
		if ( false !== get_option( self::OPTION_LAYOUT, false ) ) {
			return;
		}
		self::save_site_defaults( self::DEFAULT_LAYOUT, self::DEFAULT_STYLE );
	}

	/**
	 * Block attributes for site default (new block insert).
	 *
	 * @return array<string, mixed>
	 */
	public static function get_site_default_attributes(): array {
		return self::get_preset_attributes(
			self::get_site_default_layout(),
			self::get_site_default_style()
		);
	}

	/**
	 * Fill missing per-field presentation from the block's layout preset.
	 *
	 * Saved block attributes always win; template is fallback only.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return array<string, mixed>
	 */
	public static function apply_preset_field_presentation( array $attributes ): array {
		$layout = isset( $attributes['widgetTemplate'] ) ? \sanitize_key( (string) $attributes['widgetTemplate'] ) : self::DEFAULT_LAYOUT;
		$style  = isset( $attributes['widgetStyle'] ) ? \sanitize_key( (string) $attributes['widgetStyle'] ) : self::DEFAULT_STYLE;
		$preset = self::get_preset_attributes( $layout, $style );

		if ( empty( $preset['fieldPresentation'] ) || ! is_array( $preset['fieldPresentation'] ) ) {
			return $attributes;
		}

		$current = isset( $attributes['fieldPresentation'] ) && is_array( $attributes['fieldPresentation'] )
			? $attributes['fieldPresentation']
			: array();

		$merged = $current;

		foreach ( $preset['fieldPresentation'] as $field_key => $preset_row ) {
			if ( ! is_string( $field_key ) || ! is_array( $preset_row ) ) {
				continue;
			}

			$saved_row = isset( $merged[ $field_key ] ) && is_array( $merged[ $field_key ] )
				? $merged[ $field_key ]
				: array();

			if ( self::field_presentation_row_is_empty( $saved_row ) ) {
				$merged[ $field_key ] = $preset_row;
				continue;
			}

			$merged[ $field_key ] = array_merge( $preset_row, $saved_row );
		}

		$attributes['fieldPresentation'] = $merged;

		return $attributes;
	}

	/**
	 * Whether a saved presentation row has no meaningful overrides.
	 *
	 * @param array<string, mixed> $row Saved row.
	 * @return bool
	 */
	private static function field_presentation_row_is_empty( array $row ): bool {
		if ( empty( $row ) ) {
			return true;
		}

		$has_mode = isset( $row['mode'] ) && '' !== \sanitize_key( (string) $row['mode'] );
		$has_icon = isset( $row['icon'] ) && '' !== \sanitize_key( (string) $row['icon'] );
		$has_label = isset( $row['labelText'] ) && '' !== trim( (string) $row['labelText'] );
		$has_custom_icon = ! empty( $row['customIconId'] ) || ! empty( $row['customSvg'] );

		return ! $has_mode && ! $has_icon && ! $has_label && ! $has_custom_icon;
	}

	/**
	 * Merge layout visibility + presentation with style keys.
	 *
	 * @param string $layout Layout slug.
	 * @param string $style  Style slug.
	 * @return array<string, mixed>
	 */
	public static function get_preset_attributes( string $layout, string $style ): array {
		$layout = \sanitize_key( $layout );
		$style  = \sanitize_key( $style );

		if ( ! in_array( $layout, self::layout_slugs(), true ) ) {
			$layout = self::DEFAULT_LAYOUT;
		}
		if ( ! in_array( $style, self::style_slugs(), true ) ) {
			$style = self::DEFAULT_STYLE;
		}

		$layouts = self::get_layout_presets();
		$preset  = $layouts[ $layout ] ?? $layouts[ self::DEFAULT_LAYOUT ];

		return array_merge(
			$preset,
			array(
				'widgetTemplate' => $layout,
				'widgetStyle'    => $style,
			)
		);
	}

	/**
	 * Summary for admin UI (visible fields + icons).
	 *
	 * @param string $layout Layout slug.
	 * @return array{visible_fields: string[], field_presentation: array<string, array<string, string>>}
	 */
	public static function get_layout_summary( string $layout ): array {
		$attrs = self::get_preset_attributes( $layout, self::STYLE_DARK );
		$visible = array();
		$map     = self::visibility_attr_map();

		foreach ( $map as $field_key => $show_attr ) {
			if ( ! empty( $attrs[ $show_attr ] ) ) {
				$visible[] = $field_key;
			}
		}

		$resolved = Field_Presentation::resolve( $attrs );
		$presentation = array();

		foreach ( $visible as $field_key ) {
			$row = $resolved[ $field_key ] ?? array();
			$presentation[ $field_key ] = array(
				'mode' => isset( $row['mode'] ) ? (string) $row['mode'] : Field_Presentation::MODE_TEXT,
				'icon' => isset( $row['icon'] ) ? (string) $row['icon'] : '',
			);
		}

		return array(
			'visible_fields'      => $visible,
			'field_presentation'  => $presentation,
		);
	}

	/**
	 * REST payload: all layouts with summaries.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_admin_template_rows(): array {
		$rows = array();

		foreach ( self::get_layout_catalog() as $item ) {
			$summary = self::get_layout_summary( $item['slug'] );
			$rows[]  = array_merge(
				$item,
				$summary,
				array(
					'is_default' => self::get_site_default_layout() === $item['slug'],
				)
			);
		}

		return $rows;
	}

	/**
	 * Field key => show* attribute name.
	 *
	 * @return array<string, string>
	 */
	private static function visibility_attr_map(): array {
		return array(
			'locationName' => 'showLocationName',
			'temperature'  => 'showTemperature',
			'feelsLike'    => 'showFeelsLike',
			'condition'    => 'showCondition',
			'humidity'     => 'showHumidity',
			'pressure'     => 'showPressure',
			'windSpeed'    => 'showWindSpeed',
			'sunrise'      => 'showSunrise',
			'sunset'       => 'showSunset',
		);
	}

	/**
	 * Layout presets without style keys.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function get_layout_presets(): array {
		$hidden = array(
			'showLocationName' => false,
			'showTemperature'  => false,
			'showFeelsLike'    => false,
			'showCondition'    => false,
			'showHumidity'     => false,
			'showPressure'     => false,
			'showWindSpeed'    => false,
			'showSunrise'      => false,
			'showSunset'       => false,
		);

		$presentation = static function ( array $rows ): array {
			return array( 'fieldPresentation' => $rows );
		};

		return array(
			self::LAYOUT_SMALL => array_merge(
				$hidden,
				array(
					'showLocationName' => true,
					'showTemperature'  => true,
				),
				$presentation(
					array(
						'locationName' => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'map-pin',
						),
						'temperature'  => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'thermometer',
						),
					)
				)
			),
			self::LAYOUT_COMPACT => array_merge(
				$hidden,
				array(
					'showLocationName' => true,
					'showTemperature'  => true,
					'showCondition'    => true,
					'showHumidity'     => true,
					'showWindSpeed'    => true,
				),
				$presentation(
					array(
						'locationName' => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'map-pin',
						),
						'temperature'  => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'thermometer',
						),
						'condition'    => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'cloud-sun',
						),
						'humidity'     => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'droplets',
						),
						'windSpeed'    => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'wind',
						),
					)
				)
			),
			self::LAYOUT_ADVANCED => array_merge(
				array(
					'showLocationName' => true,
					'showTemperature'  => true,
					'showFeelsLike'    => true,
					'showCondition'    => true,
					'showHumidity'     => true,
					'showPressure'     => true,
					'showWindSpeed'    => true,
					'showSunrise'      => true,
					'showSunset'       => true,
				),
				$presentation(
					array(
						'locationName' => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'map-pin',
						),
						'temperature'  => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'thermometer',
						),
						'feelsLike'    => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'thermometer-sun',
						),
						'condition'    => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'cloud-sun',
						),
						'humidity'     => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'droplets',
						),
						'pressure'     => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'gauge',
						),
						'windSpeed'    => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'wind',
						),
						'sunrise'      => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'sunrise',
						),
						'sunset'       => array(
							'mode' => Field_Presentation::MODE_ICON_TEXT,
							'icon' => 'sunset',
						),
					)
				)
			),
		);
	}
}
