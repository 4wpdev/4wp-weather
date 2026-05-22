<?php
/**
 * Optional admin hints for obtaining an upstream API key.
 *
 * @package ForWP\Weather
 */

namespace ForWP\Weather\Contracts;

defined( 'ABSPATH' ) || exit;

/**
 * Implemented by providers that require dashboard signup documentation links.
 */
interface Weather_Credential_Help_Interface {

	/**
	 * Short paragraph shown under the API key field (plain text / translated).
	 */
	public function get_api_key_help_intro(): string;

	/**
	 * HTTPS URL for creating or managing API keys (validated before REST output).
	 */
	public function get_api_key_docs_url(): string;

	/**
	 * Accessible label for the external documentation link.
	 */
	public function get_api_key_docs_link_label(): string;
}
