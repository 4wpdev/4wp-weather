<?php
/**
 * @package ForWP\Weather
 */

declare( strict_types=1 );

namespace ForWP\Weather\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use ForWP\Weather\Field_Presentation;
use PHPUnit\Framework\TestCase;

/**
 * Field presentation resolver tests.
 */
class Field_PresentationTest extends TestCase {

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( '__' )->returnArg( 1 );
		Functions\when( 'apply_filters' )->returnArg( 2 );
		Functions\when( 'sanitize_key' )->alias(
			static function ( $key ) {
				return strtolower( (string) preg_replace( '/[^a-z0-9_\-]/', '', (string) $key ) );
			}
		);
		Functions\when( 'sanitize_text_field' )->returnArg( 1 );
		Functions\when( 'sanitize_hex_color' )->returnArg( 1 );
		Functions\when( 'wp_kses' )->returnArg( 1 );
		Functions\when( 'esc_html' )->returnArg( 1 );
		Functions\when( 'esc_attr' )->returnArg( 1 );
	}

	/**
	 * @return void
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function test_resolve_returns_defaults_when_attribute_empty(): void {
		$resolved = Field_Presentation::resolve( array() );

		$this->assertSame( Field_Presentation::MODE_TEXT, $resolved['temperature']['mode'] );
		$this->assertSame( 'thermometer', $resolved['temperature']['icon'] );
		$this->assertCount( count( Field_Presentation::FIELD_KEYS ), $resolved );
	}

	/**
	 * @return void
	 */
	public function test_resolve_merges_per_field_overrides(): void {
		$resolved = Field_Presentation::resolve(
			array(
				'fieldPresentation' => array(
					'humidity' => array(
						'mode' => Field_Presentation::MODE_ICON_TEXT,
						'icon' => 'droplets',
					),
					'windSpeed' => array(
						'mode' => Field_Presentation::MODE_ICON,
						'icon' => 'wind',
					),
				),
			)
		);

		$this->assertSame( Field_Presentation::MODE_ICON_TEXT, $resolved['humidity']['mode'] );
		$this->assertSame( 'droplets', $resolved['humidity']['icon'] );
		$this->assertSame( Field_Presentation::MODE_ICON, $resolved['windSpeed']['mode'] );
		$this->assertSame( Field_Presentation::MODE_TEXT, $resolved['temperature']['mode'] );
	}

	/**
	 * @return void
	 */
	public function test_resolve_rejects_invalid_mode_and_icon(): void {
		$resolved = Field_Presentation::resolve(
			array(
				'fieldPresentation' => array(
					'pressure' => array(
						'mode' => 'invalid-mode',
						'icon' => 'not-real-icon',
					),
				),
			)
		);

		$this->assertSame( Field_Presentation::MODE_TEXT, $resolved['pressure']['mode'] );
		$this->assertSame( 'gauge', $resolved['pressure']['icon'] );
	}

	/**
	 * @return void
	 */
	public function test_resolve_supports_custom_icon_and_styles(): void {
		$resolved = Field_Presentation::resolve(
			array(
				'fieldPresentation' => array(
					'temperature' => array(
						'mode'              => Field_Presentation::MODE_CUSTOM_ICON,
						'customSvg'         => '<svg xmlns="http://www.w3.org/2000/svg"></svg>',
						'labelColor'        => '#00ff00',
						'iconColor'         => '#ff0000',
						'iconBackground'    => '#000000',
						'iconPaddingTop'    => '4px',
						'iconPaddingRight'  => '6px',
						'iconPaddingBottom' => '4px',
						'iconPaddingLeft'   => '6px',
					),
				),
			)
		);

		$this->assertSame( Field_Presentation::MODE_CUSTOM_ICON, $resolved['temperature']['mode'] );
		$this->assertSame( '#00ff00', $resolved['temperature']['labelColor'] );
		$this->assertSame( '#ff0000', $resolved['temperature']['iconColor'] );
		$this->assertSame( '#000000', $resolved['temperature']['iconBackground'] );
		$this->assertSame( '4px', $resolved['temperature']['iconPaddingTop'] );
		$this->assertSame( '6px', $resolved['temperature']['iconPaddingRight'] );
	}

	/**
	 * @return void
	 */
	public function test_resolve_stores_custom_icon_attachment_id(): void {
		$resolved = Field_Presentation::resolve(
			array(
				'fieldPresentation' => array(
					'humidity' => array(
						'mode'         => Field_Presentation::MODE_CUSTOM_ICON,
						'customIconId' => 42,
					),
				),
			)
		);

		$this->assertSame( 42, $resolved['humidity']['customIconId'] );
	}

	/**
	 * @return void
	 */
	public function test_resolve_stores_custom_label_text(): void {
		$resolved = Field_Presentation::resolve(
			array(
				'fieldPresentation' => array(
					'temperature' => array(
						'labelText' => '  Air temp  ',
					),
				),
			)
		);

		$this->assertSame( 'Air temp', $resolved['temperature']['labelText'] );
	}

	/**
	 * @return void
	 */
	public function test_render_label_html_uses_custom_label_text(): void {
		$row = Field_Presentation::resolve(
			array(
				'fieldPresentation' => array(
					'temperature' => array(
						'mode'      => Field_Presentation::MODE_ICON_TEXT,
						'labelText' => 'Air temp',
					),
				),
			)
		)['temperature'];

		$html = Field_Presentation::render_label_html(
			'temperature',
			'Temperature',
			$row
		);

		$this->assertStringContainsString( 'Air temp', $html );
		$this->assertStringContainsString( 'forwp-weather__label-text--custom', $html );
		$this->assertStringNotContainsString( '>Temperature<', $html );
	}
}
