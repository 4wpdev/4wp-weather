/**
 * Extends @wordpress/scripts with the admin React screen entry.
 */
const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );
const { getWebpackEntryPoints } = require( '@wordpress/scripts/utils/config.js' );

module.exports = {
	...defaultConfig,
	entry() {
		const inherited =
			typeof defaultConfig.entry === 'function'
				? defaultConfig.entry()
				: defaultConfig.entry || {};

		return {
			...inherited,
			'admin/index': path.resolve(
				process.cwd(),
				'src/admin/index.js'
			),
		};
	},
};
