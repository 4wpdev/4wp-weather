=== 4WP Weather ===
Contributors: 4wp, Anatolikkk
Requires at least: 6.4
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Weather block with pluggable providers (OpenWeatherMap live today; more via roadmap), server-side cache, admin credentials, and AJAX.

== Installation ==

1. Upload the plugin folder or install via ZIP upload.
2. Run `composer install` inside the plugin directory if `vendor/` is not present.
3. Run `npm install && npm run build` to compile block + admin assets into `build/` (includes `build/admin/`).
4. Activate **4WP Weather**.
5. Open **4WP Weather** in the left admin menu (cloud icon), tab **Providers**, set **Credential provider** and API key, then save.

== WP-CLI ==

* `wp forwp-weather flush-cache` — clears server-side cached weather payloads (WordPress transients tracked by the plugin). The reported count matches entries removed on that run.

Example:

    wp forwp-weather flush-cache
    Success: Flushed 3 cached weather entries.

== Providers architecture ==

* Live integration: **OpenWeatherMap** (`openweathermap`) — Current Weather 2.5 API; SSR shell template `src/weather/templates/card-openweathermap.php`.
* Roadmap stubs (registered, disabled in UI): Tomorrow.io, Visual Crossing, Weatherbit, Open-Meteo, AccuWeather, Meteosource — same canonical payload shape when implemented later.
* Extend or replace the map via filter `forwp_weather_providers` (must return `Weather_Provider_Interface` instances keyed by slug).

== Structured data (Schema.org) ==

Optional **JSON-LD** for the block: turn on **4WP Weather → Documentation → “Structured data (JSON-LD) on the site”**. After a successful front-end fetch the plugin updates a `script type="application/ld+json"` tag (schema.org `Observation` with `observationAbout` as a `Place`, plus measured fields where available). Output is for **public** pages only (not wp-admin). There is **no** guarantee of weather rich results in Google—useful mainly for semantics and other consumers.

A minimal JSON-LD stub is also output in the card template when the option is on, then replaced in place after the AJAX response.

*(Still fond of table, tr, th, td — old-school habits die hard. — plugin author.)*

== Privacy ==

The API key is stored as a WordPress option and never printed to HTML or JavaScript. Frontend scripts call WordPress AJAX which performs the remote request server-side.
