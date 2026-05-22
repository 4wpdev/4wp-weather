<?php
/**
 * Minimal PSR-4 autoloader fallback when Composer vendor is absent.
 *
 * @package ForWP\Weather
 */

namespace ForWP\Weather;

defined( 'ABSPATH' ) || exit;

/**
 * Registers a simple autoloader for this plugin namespace.
 */
final class Autoload {

	/**
	 * Register spl autoload.
	 *
	 * @return void
	 */
	public static function register(): void {
		spl_autoload_register(
			static function ( string $class ): void {
				$prefix = __NAMESPACE__ . '\\';
				if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
					return;
				}
				$relative = substr( $class, strlen( $prefix ) );
				$file     = FORWP_WEATHER_PATH . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
				if ( is_readable( $file ) ) {
					require_once $file;
				}
			}
		);
	}
}
