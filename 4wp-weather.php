<?php
/**
 * Plugin Name:       4WP Weather (Weather Forecast Block)
 * Plugin URI:        https://4wp.dev/
 * Description:       Pluggable weather providers, Gutenberg block, server-side cached fetches, admin credentials, REST API, and WP-CLI cache flush.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Tested up to:      7.0
 * Requires PHP:      7.4
 * Author:            4wpdev
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       4wp-weather
 *
 * @package ForWP\Weather
 */

defined( 'ABSPATH' ) || exit;

define( 'FORWP_WEATHER_VERSION', '1.0.0' );
define( 'FORWP_WEATHER_FILE', __FILE__ );
define( 'FORWP_WEATHER_PATH', plugin_dir_path( __FILE__ ) );
define( 'FORWP_WEATHER_URL', plugin_dir_url( __FILE__ ) );

if ( file_exists( FORWP_WEATHER_PATH . 'vendor/autoload.php' ) ) {
	require_once FORWP_WEATHER_PATH . 'vendor/autoload.php';
} else {
	require_once FORWP_WEATHER_PATH . 'src/Autoload.php';
	ForWP\Weather\Autoload::register();
}

ForWP\Weather\Plugin::instance()->boot();
