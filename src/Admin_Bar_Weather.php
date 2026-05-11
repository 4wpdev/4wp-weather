<?php
/**
 * Optional admin bar summary (preview coordinates + credential provider).
 *
 * Isolated on purpose: small feature, separate from Admin_Settings screen wiring.
 *
 * @package Forwp\Weather
 */

namespace Forwp\Weather;

defined( 'ABSPATH' ) || exit;

/**
 * Registers a top-level admin bar item when enabled.
 */
final class Admin_Bar_Weather {

	/**
	 * Hook admin bar integration.
	 */
	public static function boot(): void {
		add_action( 'admin_bar_menu', array( self::class, 'register' ), 110 );
	}

	/**
	 * Add weather node for administrators who opted in.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public static function register( \WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! Admin_Settings::instance()->get_show_admin_bar_weather() ) {
			return;
		}

		$coords  = Admin_Settings::instance()->get_preview_coordinates();
		$slug    = Admin_Settings::instance()->get_credential_provider();
		$service = new Weather_Service();
		$result  = $service->get_weather( $coords['lat'], $coords['lon'], $slug );

		$wp_admin_bar->add_node(
			array(
				'id'     => 'forwp-weather-admin-bar',
				'parent' => false,
				'href'   => admin_url( 'admin.php?page=forwp-weather' ),
				'title'  => self::build_title( $result ),
				'meta'   => array(
					'class' => 'forwp-weather-admin-bar-root',
					'title' => __( 'Current weather for admin preview coordinates. Opens 4WP Weather settings.', '4wp-weather' ),
				),
			)
		);
	}

	/**
	 * Build HTML title (admin bar allows limited markup from trusted code).
	 *
	 * @param array|\WP_Error $result Weather payload or error.
	 */
	private static function build_title( $result ): string {
		if ( is_wp_error( $result ) ) {
			$text = __( 'Weather unavailable', '4wp-weather' );
			return self::wrap_title( $text );
		}

		if ( ! is_array( $result ) ) {
			$text = __( 'Weather —', '4wp-weather' );
			return self::wrap_title( $text );
		}

		$location = '';
		if ( ! empty( $result['locationName'] ) && is_string( $result['locationName'] ) ) {
			$location = $result['locationName'];
		}
		if ( ! empty( $result['country'] ) && is_string( $result['country'] ) ) {
			$location .= ( '' !== $location ? ', ' : '' ) . $result['country'];
		}
		if ( '' === $location ) {
			$location = __( 'Unknown place', '4wp-weather' );
		}

		if ( isset( $result['temperature'] ) && is_numeric( $result['temperature'] ) ) {
			/* translators: %s: formatted temperature number (locale-aware). */
			$temp = sprintf( __( '%s °C', '4wp-weather' ), number_format_i18n( (float) $result['temperature'], 1 ) );
		} else {
			$temp = '—';
		}

		$text = $location . ' · ' . $temp;

		return self::wrap_title( $text );
	}

	/**
	 * Icon + label wrapper consistent with core admin bar items.
	 *
	 * @param string $text Plain text (will be escaped).
	 */
	private static function wrap_title( string $text ): string {
		$icon = '<span class="ab-icon dashicons dashicons-cloud" aria-hidden="true"></span>';

		return $icon . '<span class="ab-label">' . esc_html( $text ) . '</span>';
	}
}
