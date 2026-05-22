<?php
/**
 * @package ForWP\Weather
 */

declare( strict_types=1 );

namespace ForWP\Weather\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use ForWP\Weather\Provider_Registry;
use ForWP\Weather\Providers\OpenWeatherMap_Provider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Provider registry unit tests (Brain Monkey stubs, no full WP bootstrap).
 */
class Provider_RegistryTest extends TestCase {

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( '__' )->returnArg( 1 );
		Functions\when( 'apply_filters' )->returnArg( 1 );
		Functions\when( 'sanitize_key' )->alias(
			static function ( $key ) {
				return strtolower( (string) preg_replace( '/[^a-z0-9_\-]/', '', (string) $key ) );
			}
		);
		$this->reset_registry_cache();
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
	public function test_get_returns_openweathermap_provider(): void {
		$provider = Provider_Registry::get( OpenWeatherMap_Provider::SLUG );
		$this->assertNotNull( $provider );
		$this->assertSame( OpenWeatherMap_Provider::SLUG, $provider->get_slug() );
	}

	/**
	 * @return void
	 */
	public function test_get_returns_null_for_unknown_slug(): void {
		$this->assertNull( Provider_Registry::get( 'not-a-real-provider' ) );
	}

	/**
	 * @return void
	 */
	public function test_implemented_slugs_includes_openweathermap(): void {
		$this->assertContains( OpenWeatherMap_Provider::SLUG, Provider_Registry::implemented_slugs() );
	}

	/**
	 * Clear static provider cache between tests.
	 *
	 * @return void
	 */
	private function reset_registry_cache(): void {
		$ref  = new ReflectionClass( Provider_Registry::class );
		$prop = $ref->getProperty( 'providers' );
		$prop->setAccessible( true );
		$prop->setValue( null, null );
	}
}
