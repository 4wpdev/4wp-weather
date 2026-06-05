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

A plugin by [4wp.dev](https://4wp.dev/). **4WP** is our project brand; the letters "WP" appear only as part of that brand name, not as a reference to WordPress. This plugin is not affiliated with, endorsed, or sponsored by WordPress.

Source code and releases: [github.com/4wpdev/4wp-weather](https://github.com/4wpdev/4wp-weather)

= Development =

JavaScript and CSS are built with `@wordpress/scripts`. The plugin ZIP includes human-readable source (`src/`, `package.json`, `webpack.config.js`) plus compiled `build/`. The same tree is mirrored on GitHub for development and releases.

1. `cd` into the plugin directory
2. `npm install`
3. `npm run build` — outputs `build/weather/` and `build/admin/`

= Key features =

* Gutenberg block `forwp/weather` with OpenWeatherMap (more providers on the roadmap)
* React admin: **Providers** (API key, live preview) and **Documentation**
* Server-side upstream requests; optional **JSON-LD** (`Observation`)
* WP-CLI: `wp forwp-weather flush-cache`

= Privacy =

The API key is stored as a WordPress option and is not exposed to front-end HTML or JavaScript. The block calls `forwp-weather/v1/weather`; PHP performs the remote request. See **External services** below for what is sent to OpenWeatherMap.

== External services ==

This plugin connects to the **OpenWeatherMap Current Weather API** to fetch conditions shown in the weather block, admin live preview, and optional JSON-LD.

When a visitor loads a page with the block (or when an administrator uses the live preview), WordPress sends a **server-side** HTTPS request to `https://api.openweathermap.org/data/2.5/weather`. The request includes:

* Your stored API key (`appid` query parameter), configured in the admin.
* Either geographic coordinates (`lat`, `lon`) from the block attributes, from browser geolocation when the visitor allows it, or a default location you configure.
* Or a city name (`q`) when a text location is configured instead of coordinates.

This plugin does not send visitor IP addresses in the API request. OpenWeatherMap may log requests according to their own policies. Responses are cached on your server (WordPress transients) to reduce repeat API calls.

This service is provided by **OpenWeather Ltd.** (OpenWeatherMap):

* Terms of use: https://openweathermap.org/terms
* Privacy policy: https://openweather.co.uk/privacy-policy

== Installation ==

1. Upload the plugin to `/wp-content/plugins/4wp-weather/` or install from the Plugins screen.
2. Activate **4WP Weather**.
3. Open **4WP Weather** in the admin menu → **Providers**, set provider and API key, then save.

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
