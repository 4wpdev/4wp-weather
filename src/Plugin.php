<?php
/**
 * Plugin bootstrap.
 *
 * @package ForWP\Weather
 */

namespace ForWP\Weather;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin singleton.
 */
final class Plugin {

	private static ?self $instance = null;

	/**
	 * Singleton accessor.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Boot hooks.
	 */
	public function boot(): void {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'localize_view_script' ), 20 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'localize_editor_providers' ), 20 );

		Admin_Settings::instance()->boot();
		Admin_Bar_Weather::boot();

		Rest_Weather::register();

		if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( '\WP_CLI' ) ) {
			\WP_CLI::add_command( 'forwp-weather flush-cache', array( Cli_Command::class, 'flush_cache' ) );
		}
	}

	/**
	 * Register block type from built assets.
	 */
	public function register_block(): void {
		$block_json = FORWP_WEATHER_PATH . 'build/weather/block.json';
		if ( ! is_readable( $block_json ) ) {
			return;
		}

		register_block_type( dirname( $block_json ) );
	}

	/**
	 * Provide REST configuration to the frontend script handle registered by the block.
	 */
	public function localize_view_script(): void {
		if ( is_admin() ) {
			return;
		}

		$handle = 'forwp-weather-view-script';
		if ( ! wp_script_is( $handle, 'registered' ) ) {
			return;
		}

		/* translators: %d: whole seconds remaining before the visitor may change location again. */
		$cooldown_wait_message = __( 'Please wait %d seconds before changing location again.', '4wp-weather' );

		wp_localize_script(
			$handle,
			'forwpWeather',
			array(
				'restUrl'      => rest_url( 'forwp-weather/v1/' ),
				'nonce'        => wp_create_nonce( 'wp_rest' ),
				'providers'    => Provider_Registry::get_editor_choices(),
				'outputJsonLd' => Admin_Settings::instance()->get_output_json_ld(),
				'strings'      => array(
					'cooldownWait' => $cooldown_wait_message,
				),
			)
		);
	}

	/**
	 * Editor script: provider choices for Inspector (implemented vs planned).
	 */
	public function localize_editor_providers(): void {
		$handle = 'forwp-weather-editor-script';
		if ( ! wp_script_is( $handle, 'registered' ) ) {
			return;
		}

		wp_add_inline_script(
			$handle,
			'window.forwpWeatherProviders = ' . wp_json_encode( Provider_Registry::get_editor_choices() ) . ';',
			'before'
		);
	}
}
