<?php
/**
 * Admin menu and asset bootstrap for the React settings app.
 *
 * @package ForWP\Weather
 */

namespace ForWP\Weather;

defined( 'ABSPATH' ) || exit;

/**
 * Top-level admin screen + REST-backed UI.
 */
final class Admin_Settings {

	public const OPTION_KEY = 'forwp_weather_api_key';

	public const CREDENTIAL_PROVIDER_OPTION = 'forwp_weather_credential_provider';

	public const PREVIEW_LAT_OPTION = 'forwp_weather_preview_lat';

	public const PREVIEW_LON_OPTION = 'forwp_weather_preview_lon';

	/**
	 * Minimum seconds between visitor-initiated location changes (search / geo button), 0 = off.
	 */
	public const LOCATION_CHANGE_COOLDOWN_OPTION = 'forwp_weather_location_change_cooldown_seconds';

	/**
	 * When true, administrators see preview weather in the admin bar (wp-admin + front when bar visible).
	 */
	public const SHOW_ADMIN_BAR_WEATHER_OPTION = 'forwp_weather_show_admin_bar_weather';

	/**
	 * When true, front-end blocks may output Schema.org JSON-LD after a successful weather fetch.
	 */
	public const OUTPUT_JSON_LD_OPTION = 'forwp_weather_output_json_ld';

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Whether menu icon inline CSS was already registered.
	 *
	 * @var bool
	 */
	private static bool $menu_icon_fix_registered = false;

	/**
	 * Shared admin settings instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor; use instance().
	 */
	private function __construct() {}

	/**
	 * Register admin menu, assets, and settings REST.
	 *
	 * @return void
	 */
	public function boot(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_menu_icon_dimensions' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		Rest_Settings::register();
	}

	/**
	 * Normalize custom SVG menu icon size (WP expects ~20×20 inside the menu sprite box).
	 */
	public function enqueue_menu_icon_dimensions(): void {
		if ( self::$menu_icon_fix_registered ) {
			return;
		}
		self::$menu_icon_fix_registered = true;

		$handle = 'forwp-weather-menu-icon';
		wp_register_style( $handle, false, array(), FORWP_WEATHER_VERSION );
		wp_enqueue_style( $handle );
		wp_add_inline_style(
			$handle,
			'#adminmenu .toplevel_page_forwp-weather .wp-menu-image img {
				width: 20px;
				height: 20px;
				max-height: none;
				padding: 0 !important;
				margin: 9px auto 0 !important;
				opacity: 0.65;
				object-fit: contain;
				display: inline-block;
				vertical-align: middle;
			}
			#adminmenu .toplevel_page_forwp-weather.wp-has-current-submenu .wp-menu-image img,
			#adminmenu .toplevel_page_forwp-weather.current .wp-menu-image img {
				opacity: 1;
			}'
		);
	}

	/**
	 * Register top-level admin menu page.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		add_menu_page(
			__( '4WP Weather', '4wp-weather' ),
			__( '4WP Weather', '4wp-weather' ),
			'manage_options',
			'forwp-weather',
			array( $this, 'render_settings_page' ),
			FORWP_WEATHER_URL . 'assets/icon-weather.svg',
			58
		);
	}

	/**
	 * Markup for the plugin settings screen (React mounts into #forwp-weather-admin-root).
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap forwp-weather-admin-shell">';
		echo '<h1 class="forwp-weather-admin-heading">';
		echo '<span class="forwp-weather-admin-heading__icon" aria-hidden="true">';
		echo wp_kses( self::weather_heading_svg(), self::weather_heading_svg_allowed_html() );
		echo '</span>';
		echo '<span class="forwp-weather-admin-heading__text">';
		echo esc_html__( '4WP Weather', '4wp-weather' );
		echo '</span>';
		echo '</h1>';
		echo '<div id="forwp-weather-admin-root" class="forwp-weather-admin-root" aria-live="polite"></div>';
		echo '</div>';
	}

	/**
	 * Allowed tags for the static admin heading SVG.
	 *
	 * @return array<string, array<string, bool>>
	 */
	private static function weather_heading_svg_allowed_html(): array {
		return array(
			'svg'  => array(
				'xmlns'       => true,
				'viewbox'     => true,
				'width'       => true,
				'height'      => true,
				'fill'        => true,
				'focusable'   => true,
				'aria-hidden' => true,
			),
			'path' => array(
				'd'               => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
			),
		);
	}

	/**
	 * Inline weather icon for the settings screen heading (stroke uses currentColor for admin theme).
	 *
	 * @return string SVG markup.
	 */
	private static function weather_heading_svg(): string {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" focusable="false" aria-hidden="true"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}

	/**
	 * Scripts and styles only on our screen.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		if ( 'toplevel_page_forwp-weather' !== $hook_suffix ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$asset_file = FORWP_WEATHER_PATH . 'build/admin/index.asset.php';
		if ( ! is_readable( $asset_file ) ) {
			return;
		}

		$asset = include $asset_file;

		wp_enqueue_style( 'wp-components' );

		$style_path = FORWP_WEATHER_PATH . 'build/admin/style-index.css';
		if ( is_readable( $style_path ) ) {
			wp_enqueue_style(
				'forwp-weather-admin',
				FORWP_WEATHER_URL . 'build/admin/style-index.css',
				array( 'wp-components' ),
				$asset['version']
			);
		}

		wp_enqueue_script(
			'forwp-weather-admin',
			FORWP_WEATHER_URL . 'build/admin/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_set_script_translations( 'forwp-weather-admin', '4wp-weather', FORWP_WEATHER_PATH . 'languages' );

		wp_localize_script(
			'forwp-weather-admin',
			'forwpWeatherAdmin',
			array(
				'restRoot' => esc_url_raw( rest_url() ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Sanitize API key input.
	 *
	 * @param mixed $value Raw value.
	 */
	public function sanitize_api_key( $value ): string {
		$value = is_string( $value ) ? $value : '';
		return sanitize_text_field( trim( $value ) );
	}

	/**
	 * Stored API key for the active credential provider.
	 *
	 * @return string
	 */
	public function get_api_key(): string {
		$key = get_option( self::OPTION_KEY, '' );
		return is_string( $key ) ? $key : '';
	}

	/**
	 * Slug of the provider that receives the stored API key.
	 *
	 * @return string
	 */
	public function get_credential_provider(): string {
		$slug = get_option( self::CREDENTIAL_PROVIDER_OPTION, '' );
		$slug = is_string( $slug ) ? sanitize_key( $slug ) : '';

		if ( '' === $slug || ! in_array( $slug, Provider_Registry::implemented_slugs(), true ) ) {
			return \ForWP\Weather\Providers\OpenWeatherMap_Provider::SLUG;
		}

		return $slug;
	}

	/**
	 * Saved preview latitude for admin live preview (optional).
	 */
	public function get_preview_latitude_saved(): string {
		$raw = get_option( self::PREVIEW_LAT_OPTION, '' );
		if ( is_numeric( $raw ) ) {
			return (string) $raw;
		}

		return '';
	}

	/**
	 * Saved preview longitude for admin live preview (optional).
	 */
	public function get_preview_longitude_saved(): string {
		$raw = get_option( self::PREVIEW_LON_OPTION, '' );
		if ( is_numeric( $raw ) ) {
			return (string) $raw;
		}

		return '';
	}

	/**
	 * Coordinates used for admin preview (saved pair or filter default).
	 *
	 * @return array{lat: float, lon: float}
	 */
	public function get_preview_coordinates(): array {
		$lat_raw = get_option( self::PREVIEW_LAT_OPTION, '' );
		$lon_raw = get_option( self::PREVIEW_LON_OPTION, '' );
		$lat     = is_numeric( $lat_raw ) ? (float) $lat_raw : null;
		$lon     = is_numeric( $lon_raw ) ? (float) $lon_raw : null;

		if ( null !== $lat && null !== $lon ) {
			return array(
				'lat' => $lat,
				'lon' => $lon,
			);
		}

		/**
		 * Filter default map coordinates when no preview lat/lon are saved.
		 *
		 * @param array{lat: float, lon: float} $coords Default coordinates (decimal degrees).
		 */
		$default = apply_filters(
			'forwp_weather_default_preview_coordinates',
			array(
				'lat' => 51.5074,
				'lon' => -0.1278,
			)
		);

		if ( ! is_array( $default ) ) {
			return array(
				'lat' => 51.5074,
				'lon' => -0.1278,
			);
		}

		$fallback_lat = isset( $default['lat'] ) && is_numeric( $default['lat'] ) ? (float) $default['lat'] : 51.5074;
		$fallback_lon = isset( $default['lon'] ) && is_numeric( $default['lon'] ) ? (float) $default['lon'] : -0.1278;

		return array(
			'lat' => $fallback_lat,
			'lon' => $fallback_lon,
		);
	}

	/**
	 * Cooldown seconds for visitor location changes (front end). Clamped 0 … DAY_IN_SECONDS.
	 */
	public function get_location_change_cooldown_seconds(): int {
		$raw = get_option( self::LOCATION_CHANGE_COOLDOWN_OPTION, 60 );
		$n   = is_numeric( $raw ) ? (int) $raw : 60;
		if ( $n < 0 ) {
			return 0;
		}
		if ( $n > (int) DAY_IN_SECONDS ) {
			return (int) DAY_IN_SECONDS;
		}

		return $n;
	}

	/**
	 * Whether to show a weather summary in the admin bar for administrators.
	 */
	public function get_show_admin_bar_weather(): bool {
		return (bool) get_option( self::SHOW_ADMIN_BAR_WEATHER_OPTION, false );
	}

	/**
	 * Whether to output JSON-LD on the public site (per successful block fetch), not in wp-admin.
	 */
	public function get_output_json_ld(): bool {
		return (bool) get_option( self::OUTPUT_JSON_LD_OPTION, false );
	}
}
