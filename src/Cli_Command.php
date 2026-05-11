<?php
/**
 * WP-CLI commands.
 *
 * @package Forwp\Weather
 */

namespace Forwp\Weather;

defined( 'ABSPATH' ) || exit;

/**
 * CLI helpers.
 */
final class Cli_Command {

	/**
	 * Flush cached weather payloads.
	 *
	 * ## EXAMPLES
	 *
	 *     wp forwp-weather flush-cache
	 *
	 * @return void
	 */
	public static function flush_cache(): void {
		$count = Weather_Service::flush_all_caches();
		\WP_CLI::success(
			sprintf(
				/* translators: %d: number of cache entries cleared */
				_n(
					'Flushed %d cached weather entry :)',
					'Flushed %d cached weather entries ;)',
					$count,
					'4wp-weather'
				),
				$count
			)
		);
	}
}
