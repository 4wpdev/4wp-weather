<?php
/**
 * Widget template presets.
 *
 * @package ForWP\Weather
 */

declare( strict_types=1 );

namespace ForWP\Weather\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use ForWP\Weather\Field_Presentation;
use ForWP\Weather\Widget_Templates;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ForWP\Weather\Widget_Templates
 */
class Widget_TemplatesTest extends TestCase {

	/**
	 * @var array<string, mixed>
	 */
	private array $options = array();

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		$this->options = array();

		Functions\when( '__' )->returnArg( 1 );
		Functions\when( 'apply_filters' )->returnArg( 2 );
		Functions\when( 'sanitize_text_field' )->returnArg( 1 );
		Functions\when( 'sanitize_hex_color' )->returnArg( 1 );
		Functions\when( 'wp_kses' )->returnArg( 1 );
		Functions\when( 'sanitize_key' )->alias(
			static function ( $key ) {
				return strtolower( (string) preg_replace( '/[^a-z0-9_\-]/', '', (string) $key ) );
			}
		);
		Functions\when( 'get_option' )->alias(
			function ( $name, $default = false ) {
				return $this->options[ $name ] ?? $default;
			}
		);
		Functions\when( 'update_option' )->alias(
			function ( $name, $value ) {
				$this->options[ $name ] = $value;
				return true;
			}
		);
		Functions\when( 'delete_option' )->alias(
			function ( $name ) {
				unset( $this->options[ $name ] );
				return true;
			}
		);
	}

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Advanced preset shows all fields with icons.
	 */
	public function test_advanced_preset_has_nine_visible_fields(): void {
		$attrs = Widget_Templates::get_preset_attributes(
			Widget_Templates::LAYOUT_ADVANCED,
			Widget_Templates::STYLE_DARK
		);

		$this->assertTrue( $attrs['showLocationName'] );
		$this->assertTrue( $attrs['showTemperature'] );
		$this->assertTrue( $attrs['showFeelsLike'] );
		$this->assertTrue( $attrs['showCondition'] );
		$this->assertTrue( $attrs['showHumidity'] );
		$this->assertTrue( $attrs['showPressure'] );
		$this->assertTrue( $attrs['showWindSpeed'] );
		$this->assertTrue( $attrs['showSunrise'] );
		$this->assertTrue( $attrs['showSunset'] );
		$this->assertSame( Widget_Templates::LAYOUT_ADVANCED, $attrs['widgetTemplate'] );
		$this->assertSame( Widget_Templates::STYLE_DARK, $attrs['widgetStyle'] );

		$resolved = Field_Presentation::resolve( $attrs );
		$this->assertSame( Field_Presentation::MODE_ICON_TEXT, $resolved['temperature']['mode'] );
		$this->assertSame( 'thermometer', $resolved['temperature']['icon'] );
	}

	/**
	 * Small preset is minimal.
	 */
	public function test_small_preset_limits_fields(): void {
		$attrs = Widget_Templates::get_preset_attributes(
			Widget_Templates::LAYOUT_SMALL,
			Widget_Templates::STYLE_WHITE
		);

		$this->assertTrue( $attrs['showLocationName'] );
		$this->assertTrue( $attrs['showTemperature'] );
		$this->assertFalse( $attrs['showCondition'] );
		$this->assertFalse( $attrs['showHumidity'] );
	}

	/**
	 * Empty fieldPresentation inherits layout preset modes.
	 */
	public function test_apply_preset_field_presentation_when_empty(): void {
		$merged = Widget_Templates::apply_preset_field_presentation(
			array(
				'widgetTemplate'    => Widget_Templates::LAYOUT_ADVANCED,
				'widgetStyle'       => Widget_Templates::STYLE_DARK,
				'fieldPresentation' => array(),
			)
		);

		$resolved = Field_Presentation::resolve( $merged );
		$this->assertSame( Field_Presentation::MODE_ICON_TEXT, $resolved['temperature']['mode'] );
		$this->assertSame( Field_Presentation::MODE_ICON_TEXT, $resolved['locationName']['mode'] );
	}

	/**
	 * Saved per-field presentation overrides template preset for that field.
	 */
	public function test_apply_preset_field_presentation_skips_when_set(): void {
		$merged = Widget_Templates::apply_preset_field_presentation(
			array(
				'widgetTemplate'    => Widget_Templates::LAYOUT_ADVANCED,
				'fieldPresentation' => array(
					'temperature' => array(
						'mode' => Field_Presentation::MODE_TEXT,
					),
				),
			)
		);

		$resolved = Field_Presentation::resolve( $merged );
		$this->assertSame( Field_Presentation::MODE_TEXT, $resolved['temperature']['mode'] );
		$this->assertSame( Field_Presentation::MODE_ICON_TEXT, $resolved['humidity']['mode'] );
	}

	/**
	 * Site defaults persist and seed once.
	 */
	public function test_save_and_seed_site_defaults(): void {
		$this->assertFalse( $this->options[ Widget_Templates::OPTION_LAYOUT ] ?? false );

		Widget_Templates::maybe_seed_site_defaults();

		$this->assertSame(
			Widget_Templates::LAYOUT_ADVANCED,
			Widget_Templates::get_site_default_layout()
		);
		$this->assertSame(
			Widget_Templates::STYLE_DARK,
			Widget_Templates::get_site_default_style()
		);

		Widget_Templates::save_site_defaults(
			Widget_Templates::LAYOUT_COMPACT,
			Widget_Templates::STYLE_WHITE
		);

		$this->assertSame(
			Widget_Templates::LAYOUT_COMPACT,
			Widget_Templates::get_site_default_layout()
		);
		$this->assertSame(
			Widget_Templates::STYLE_WHITE,
			Widget_Templates::get_site_default_style()
		);
	}
}
