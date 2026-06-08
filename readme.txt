=== 4WP Weather ===
Contributors: 4wpdev, anatolikkk
Tags: weather, weather widget, gutenberg block, openweathermap, current weather
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add a customizable Gutenberg weather widget to WordPress—live conditions, icon labels, and secure server-side API calls.

== Description ==

Show **live weather** on any page or template with a native **Gutenberg block**—no iframe embeds and no API keys in the browser.

**4WP Weather** fetches current conditions through WordPress (REST + server cache), stores your OpenWeatherMap key safely in the admin, and renders a clean widget you can style with block colors, typography, and spacing.

A plugin by [4wp.dev](https://4wp.dev/).

= Perfect for =

* A **sidebar or footer weather snippet** with temperature, humidity, wind, and more
* **Landing pages** that need local conditions without a third-party widget
* **Block themes** and Full Site Editing layouts
* Editors who want **icon + text labels** per field, editable inline in the block

= How it works =

1. Install and activate **4WP Weather**.
2. Open **4WP Weather** in the admin menu → **Providers**, add your OpenWeatherMap API key, and save.
3. Insert the **4WP Weather** block in the editor.
4. Pick a **widget template** (Small, Compact, or Advanced) and **style** (Dark or White).
5. Choose which parameters to show and how each label appears (icon, icon + text, or text).
6. Publish—the block loads data on the front end via your server.

= Key features =

* **Gutenberg block** with block supports: background/text colors, typography, spacing, border, wide/full align
* **Widget templates** — Small (location + temperature), Compact (core fields), Advanced (all nine parameters)
* **Per-field presentation** — built-in weather icons, icon + text, text-only, or custom SVG from the Media Library
* **Editable labels** — customize parameter names inline in the editor (Text and Icon + text modes)
* **OpenWeatherMap** provider (live); additional providers registered for future releases
* **Visitor location** — optional browser geolocation (automatic or button prompt) with coordinate fallback
* **Location search** — optional city/place search on the front end
* **React admin** — Providers, Settings (default template), and Documentation tabs
* **Server-side requests** with transient cache; API key never exposed to visitors
* Optional **JSON-LD** structured data (`Observation`) for SEO
* Optional **admin bar** weather preview for administrators
* **WP-CLI:** `wp forwp-weather flush-cache`

= Privacy =

The API key is stored as a WordPress option and is not exposed to front-end HTML or JavaScript. The block calls `forwp-weather/v1/weather`; PHP performs the remote request. See **External services** below.

**4WP** is our project brand; the letters "WP" appear only as part of that brand name, not as a reference to WordPress. This plugin is not affiliated with, endorsed, or sponsored by WordPress.

Source code: [github.com/4wpdev/4wp-weather](https://github.com/4wpdev/4wp-weather)

== External services ==

This plugin connects to the **OpenWeatherMap Current Weather API** to fetch conditions shown in the weather block, admin live preview, and optional JSON-LD.

When a visitor loads a page with the block (or when an administrator uses the live preview), WordPress sends a **server-side** HTTPS request to `https://api.openweathermap.org/data/2.5/weather`. The request includes:

* Your stored API key (`appid` query parameter), configured in the admin.
* Geographic coordinates (`lat`, `lon`) from block settings, browser geolocation when the visitor allows it, or a default location you configure.
* Or a city name (`q`) when location search is used.

This plugin does not send visitor IP addresses in the API request. OpenWeatherMap may log requests according to their own policies. Responses are cached on your server (WordPress transients) to reduce repeat API calls.

This service is provided by **OpenWeather Ltd.** (OpenWeatherMap):

* Terms of use: https://openweathermap.org/terms
* Privacy policy: https://openweather.co.uk/privacy-policy

== Installation ==

1. Upload the plugin to `/wp-content/plugins/4wp-weather/` or install from the Plugins screen.
2. Activate **4WP Weather**.
3. Open **4WP Weather** in the admin menu → **Providers**, choose **OpenWeatherMap**, enter your API key, and save.
4. (Optional) Under **Settings**, set the default widget template for new blocks.
5. Insert the **4WP Weather** block and configure location and visible parameters.

== Frequently Asked Questions ==

= Does the API key appear in the browser? =

No. Only WordPress talks to the weather API using the stored key.

= Do I need an OpenWeatherMap account? =

Yes. Create a free API key at [openweathermap.org](https://openweathermap.org/) and paste it under **Providers**.

= Can visitors see weather for their own location? =

Yes. Enable **Use visitor location (browser)** in the block settings. You can prompt on page load or show a **Use my location** button. If the visitor declines, fallback coordinates from the block are used.

= What is the difference between Small, Compact, and Advanced templates? =

* **Small** — location and temperature only
* **Compact** — location, temperature, condition, humidity, and wind
* **Advanced** — all nine parameters (including feels like, pressure, sunrise, and sunset)

= Can I change field labels like "Temperature" or "Wind"? =

Yes. In **Text** or **Icon + text** mode, click the label in the block preview and type your own text.

= Is JSON-LD required? =

No. Enable it under **Documentation** if you want structured data on public pages. There is no guarantee of rich results in search engines.

= Which providers work today? =

**OpenWeatherMap** is live. Additional providers are registered as roadmap placeholders.

== Screenshots ==

1. Weather widget on the front end with live OpenWeatherMap data
2. Gutenberg block in the editor — widget template, icons, and editable labels
3. Admin **Providers** tab — API key and live preview
4. Admin **Settings** tab — default widget template for new blocks

== Changelog ==

= 2.0.0 =
* Widget templates: Small, Compact, and Advanced layouts with Dark and White styles
* Per-field presentation: built-in icons, icon + text, text, and custom SVG icons
* Inline editable parameter labels in the block editor
* Admin **Settings** tab for site-wide default template
* Block supports: colors, typography, spacing, border, wide/full alignment
* Visitor geolocation (auto or button) and optional location search
* Server-side REST weather API with transient cache
* Optional JSON-LD and admin bar weather preview
* WP-CLI cache flush command

= 1.0.0 =
* Initial release: weather block, OpenWeatherMap, REST API, cache, admin UI, optional JSON-LD.

== Upgrade Notice ==

= 2.0.0 =
Major update: widget templates, icon labels, editable field names, Settings tab, and improved block editor experience.

= 1.0.0 =
Initial release.
