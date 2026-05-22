=== 4WP Weather ===
Contributors: 4wpdev, anatolikkk
Tags: weather, gutenberg, block, openweathermap, forecast
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Gutenberg weather block with pluggable providers, server-side API calls, and optional JSON-LD.

== Description ==

**4WP Weather** adds a block for current conditions on your site. Choose a provider, store your API key in WordPress (never in the browser), and load data through the REST API with server-side caching.

A plugin by [4wp.dev](https://4wp.dev/). **4WP** is our project brand; this plugin is not affiliated with, endorsed, or sponsored by WordPress.

Source code: [github.com/4wpdev/4wp-weather](https://github.com/4wpdev/4wp-weather) (when published).

= Development =

JavaScript and CSS are built with `@wordpress/scripts`. Human-readable source (`src/`, `package.json`, `webpack.config.js`) lives in the public GitHub repository — not in the distributed plugin ZIP.

1. `cd` into the plugin directory
2. `npm install`
3. `npm run build` — outputs `build/weather/` and `build/admin/`

= Key features =

* Gutenberg block `forwp/weather` with OpenWeatherMap (more providers on the roadmap)
* React admin: **Providers** (API key, live preview) and **Documentation**
* Server-side upstream requests; optional **JSON-LD** (`Observation`)
* WP-CLI: `wp forwp-weather flush-cache`

= Privacy =

The API key is stored as a WordPress option and is not exposed to front-end HTML or JavaScript. The block calls `forwp-weather/v1/weather`; PHP performs the remote request.

== Installation ==

1. Upload the plugin or install via ZIP.
2. Run `composer install` in the plugin folder if `vendor/` is missing.
3. Run `npm install && npm run build` if `build/` is missing.
4. Activate **4WP Weather**.
5. Open **4WP Weather** in the admin menu → **Providers**, set provider and API key, save.

== Frequently Asked Questions ==

= Does the API key appear in the browser? =

No. Only WordPress talks to the weather API using the stored key.

= Is JSON-LD required? =

No. Enable it under **Documentation** if you want structured data on public pages. There is no guarantee of rich results in search engines.

= Which providers work today? =

**OpenWeatherMap** is live. Additional providers are registered for future releases.

== Screenshots ==

1. Weather block on the front end
2. Providers tab with API key and preview
3. Block in the editor

== Changelog ==

= 1.0.0 =
* Initial release: weather block, OpenWeatherMap, REST API, cache, admin UI, optional JSON-LD.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
