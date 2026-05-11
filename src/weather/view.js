/**
 * Frontend fetch + hydrate.
 */
import domReady from '@wordpress/dom-ready';

const GEO_OPTIONS = {
	enableHighAccuracy: false,
	timeout: 15000,
	maximumAge: 600000,
};

/** Prefix for {@link https://developer.mozilla.org/en-US/docs/Web/API/Window/localStorage localStorage} keys. */
const STORAGE_PREFIX = 'forwpWeather/v1/place/';

/** Timestamp (ms) of last visitor-initiated location change (search / geo). */
const STORAGE_LAST_MUT_PREFIX = 'forwpWeather/v1/lastMut/';

/** One live countdown interval per block root (cleared on hydrate / new tick). */
const cooldownTickers = new WeakMap();

function getInstanceKey( root ) {
	return root.getAttribute( 'data-forwp-instance' ) || '';
}

/**
 * Read a visitor-saved place query for this block instance.
 */
function readStoredPlace( root ) {
	const ik = getInstanceKey( root );
	if ( ! ik || typeof localStorage === 'undefined' ) {
		return '';
	}
	try {
		const raw = localStorage.getItem( STORAGE_PREFIX + ik );
		return typeof raw === 'string' ? raw.trim() : '';
	} catch {
		return '';
	}
}

/**
 * Save or clear the place query (empty string removes the key).
 */
function getCooldownSeconds( root ) {
	const raw = root.getAttribute( 'data-location-cooldown-sec' );
	const n = parseInt( raw, 10 );
	if ( ! Number.isFinite( n ) || n < 0 ) {
		return 0;
	}
	/* Upper bound matches PHP (DAY_IN_SECONDS). */
	return Math.min( n, 86400 );
}

function readLastMutationMs( root ) {
	const ik = getInstanceKey( root );
	if ( ! ik || typeof localStorage === 'undefined' ) {
		return null;
	}
	try {
		const raw = localStorage.getItem( STORAGE_LAST_MUT_PREFIX + ik );
		if ( raw === null ) {
			return null;
		}
		const ms = parseInt( raw, 10 );
		return Number.isFinite( ms ) ? ms : null;
	} catch {
		return null;
	}
}

function recordInteractiveMutation( root ) {
	const ik = getInstanceKey( root );
	if ( ! ik || typeof localStorage === 'undefined' ) {
		return;
	}
	try {
		localStorage.setItem(
			STORAGE_LAST_MUT_PREFIX + ik,
			String( Date.now() )
		);
	} catch {
		/* ignore */
	}
}

function getCooldownRemainSeconds( root ) {
	const cooldown = getCooldownSeconds( root );
	if ( cooldown <= 0 ) {
		return 0;
	}
	const last = readLastMutationMs( root );
	if ( last === null ) {
		return 0;
	}
	const elapsedSec = ( Date.now() - last ) / 1000;
	const remain = Math.ceil( cooldown - elapsedSec );
	return remain > 0 ? remain : 0;
}

function formatCooldownMessage( seconds ) {
	const tmpl =
		window.forwpWeather &&
		window.forwpWeather.strings &&
		window.forwpWeather.strings.cooldownWait;
	if ( typeof tmpl === 'string' && tmpl.includes( '%d' ) ) {
		return tmpl.replace( /%d/g, String( seconds ) );
	}
	return `Please wait ${ seconds } seconds before changing location again.`;
}

function clearCooldownTicker( root ) {
	const id = cooldownTickers.get( root );
	if ( id !== undefined && id !== null ) {
		window.clearInterval( id );
		cooldownTickers.delete( root );
	}
}

/**
 * Updates the cooldown message every second until the visitor may retry.
 */
function startCooldownTicker( root, errorBox ) {
	clearCooldownTicker( root );
	const tick = () => {
		const remain = getCooldownRemainSeconds( root );
		if ( remain <= 0 ) {
			clearCooldownTicker( root );
			if ( errorBox ) {
				errorBox.hidden = true;
				errorBox.textContent = '';
			}
			return;
		}
		if ( errorBox ) {
			errorBox.hidden = false;
			errorBox.textContent = formatCooldownMessage( remain );
		}
	};
	tick();
	cooldownTickers.set(
		root,
		window.setInterval( tick, 1000 )
	);
}

/**
 * Blocks visitor-initiated location changes until cooldown elapsed.
 */
function enforceCooldownOrShowError( root, errorBox ) {
	const remain = getCooldownRemainSeconds( root );
	if ( remain <= 0 ) {
		clearCooldownTicker( root );
		return true;
	}
	root.setAttribute( 'aria-busy', 'false' );
	startCooldownTicker( root, errorBox );
	return false;
}

function persistStoredPlace( root, query ) {
	const ik = getInstanceKey( root );
	if ( ! ik || typeof localStorage === 'undefined' ) {
		return;
	}
	const trimmed =
		typeof query === 'string' ? query.trim() : '';
	try {
		const storageKey = STORAGE_PREFIX + ik;
		if ( trimmed === '' ) {
			localStorage.removeItem( storageKey );
		} else {
			localStorage.setItem( storageKey, trimmed );
		}
	} catch {
		/* Private mode / quota — ignore */
	}
}

function formatMaybeTime( unixSeconds ) {
	if ( ! unixSeconds ) {
		return '—';
	}
	try {
		return new Date( unixSeconds * 1000 ).toLocaleTimeString(
			undefined,
			{
				hour: '2-digit',
				minute: '2-digit',
			}
		);
	} catch {
		return '—';
	}
}

function formatNumber( value, suffix = '' ) {
	if ( value === null || typeof value === 'undefined' ) {
		return '—';
	}
	return `${ Number( value ).toLocaleString() }${ suffix }`;
}

function removeJsonLd( root ) {
	const ik = getInstanceKey( root );
	if ( ! ik ) {
		return;
	}
	const existing = root.querySelector( '#forwp-weather-jsonld-' + ik );
	if ( existing ) {
		existing.remove();
	}
}

/**
 * Schema.org Observation + observationAbout Place (front-end only; toggled in plugin settings).
 *
 * @param {Record<string, unknown>} payload Normalized weather payload from AJAX.
 */
function buildJsonLd( payload ) {
	const fetchedAt =
		typeof payload.fetchedAt === 'number' && Number.isFinite( payload.fetchedAt )
			? payload.fetchedAt
			: Math.floor( Date.now() / 1000 );
	const observationAbout = {
		'@type': 'Place',
		name:
			typeof payload.locationName === 'string' && payload.locationName
				? typeof payload.country === 'string' && payload.country
					? `${ payload.locationName }, ${ payload.country }`
					: payload.locationName
				: 'Weather',
	};
	if (
		typeof payload.latitude === 'number' &&
		Number.isFinite( payload.latitude ) &&
		typeof payload.longitude === 'number' &&
		Number.isFinite( payload.longitude )
	) {
		observationAbout.geo = {
			'@type': 'GeoCoordinates',
			latitude: payload.latitude,
			longitude: payload.longitude,
		};
	}
	const additionalProperty = [];
	if (
		typeof payload.temperature === 'number' &&
		Number.isFinite( payload.temperature )
	) {
		additionalProperty.push( {
			'@type': 'PropertyValue',
			name: 'temperature',
			value: payload.temperature,
			unitText: '°C',
		} );
	}
	if (
		typeof payload.humidity === 'number' &&
		Number.isFinite( payload.humidity )
	) {
		additionalProperty.push( {
			'@type': 'PropertyValue',
			name: 'humidity',
			value: payload.humidity,
			unitText: '%',
		} );
	}
	if (
		typeof payload.pressure === 'number' &&
		Number.isFinite( payload.pressure )
	) {
		additionalProperty.push( {
			'@type': 'PropertyValue',
			name: 'pressure',
			value: payload.pressure,
			unitText: 'hPa',
		} );
	}
	if (
		typeof payload.windSpeed === 'number' &&
		Number.isFinite( payload.windSpeed )
	) {
		additionalProperty.push( {
			'@type': 'PropertyValue',
			name: 'windSpeed',
			value: payload.windSpeed,
			unitText: 'm/s',
		} );
	}
	const graph = {
		'@context': 'https://schema.org',
		'@type': 'Observation',
		name: 'Current weather',
		observationDate: new Date( fetchedAt * 1000 ).toISOString(),
		observationAbout,
	};
	if ( additionalProperty.length ) {
		graph.additionalProperty = additionalProperty;
	}
	return graph;
}

function upsertJsonLd( root, payload ) {
	if ( ! window.forwpWeather || ! window.forwpWeather.outputJsonLd ) {
		removeJsonLd( root );
		return;
	}
	const ik = getInstanceKey( root );
	if ( ! ik ) {
		return;
	}
	const json = JSON.stringify( buildJsonLd( payload ) );
	const existing = root.querySelector( '#forwp-weather-jsonld-' + ik );
	if ( existing ) {
		existing.textContent = json;
		return;
	}
	const script = document.createElement( 'script' );
	script.type = 'application/ld+json';
	script.id = 'forwp-weather-jsonld-' + ik;
	script.textContent = json;
	root.appendChild( script );
}

function syncCoordsFromPayload( root, payload ) {
	if (
		typeof payload.latitude === 'number' &&
		Number.isFinite( payload.latitude )
	) {
		root.setAttribute( 'data-lat', String( payload.latitude ) );
	}
	if (
		typeof payload.longitude === 'number' &&
		Number.isFinite( payload.longitude )
	) {
		root.setAttribute( 'data-lon', String( payload.longitude ) );
	}
}

function applyPayload( root, payload ) {
	const mapping = {
		locationName: () =>
			payload.country
				? `${ payload.locationName }, ${ payload.country }`
				: payload.locationName || '—',
		temperature: () => formatNumber( payload.temperature, ' °C' ),
		feelsLike: () => formatNumber( payload.feelsLike, ' °C' ),
		condition: () => payload.condition || '—',
		humidity: () => formatNumber( payload.humidity, ' %' ),
		pressure: () => formatNumber( payload.pressure, ' hPa' ),
		windSpeed: () => formatNumber( payload.windSpeed, ' m/s' ),
		sunrise: () => formatMaybeTime( payload.sunrise ),
		sunset: () => formatMaybeTime( payload.sunset ),
	};

	Object.entries( mapping ).forEach( ( [ key, fn ] ) => {
		const cell = root.querySelector(
			`[data-forwp-field="${ key }"]`
		);
		if ( cell ) {
			cell.textContent = fn();
		}
	} );

	syncCoordsFromPayload( root, payload );

	root.setAttribute( 'aria-busy', 'false' );
	const status = root.querySelector( '.forwp-weather__status' );
	if ( status ) {
		status.remove();
	}

	upsertJsonLd( root, payload );
}

function getCurrentPositionAsync( options ) {
	return new Promise( ( resolve, reject ) => {
		if (
			typeof navigator === 'undefined' ||
			! navigator.geolocation ||
			typeof navigator.geolocation.getCurrentPosition !== 'function'
		) {
			reject( new Error( 'Geolocation unavailable' ) );
			return;
		}
		navigator.geolocation.getCurrentPosition( resolve, reject, options );
	} );
}

async function tryResolveBrowserGeo( root, lat, lon ) {
	try {
		const pos = await getCurrentPositionAsync( GEO_OPTIONS );
		const newLat = String( pos.coords.latitude );
		const newLon = String( pos.coords.longitude );
		root.setAttribute( 'data-lat', newLat );
		root.setAttribute( 'data-lon', newLon );
		return { lat: newLat, lon: newLon };
	} catch {
		return { lat, lon };
	}
}

function setupLocationSearch( root, provider, errorBox ) {
	const searchForm = root.querySelector( '.forwp-weather__search' );
	if ( ! searchForm ) {
		return;
	}

	const input = searchForm.querySelector( '.forwp-weather__search-input' );
	const submitBtn = searchForm.querySelector(
		'.forwp-weather__search-submit'
	);

	const saved = readStoredPlace( root );
	if ( input && saved !== '' ) {
		input.value = saved;
	}

	searchForm.addEventListener( 'submit', async ( event ) => {
		event.preventDefault();
		const query = input ? input.value.trim() : '';

		if ( ! enforceCooldownOrShowError( root, errorBox ) ) {
			return;
		}

		if ( submitBtn ) {
			submitBtn.disabled = true;
		}

		root.setAttribute( 'aria-busy', 'true' );

		if ( errorBox ) {
			errorBox.hidden = true;
			errorBox.textContent = '';
		}

		const latNow = root.getAttribute( 'data-lat' );
		const lonNow = root.getAttribute( 'data-lon' );

		try {
			const ok = await fetchWeather(
				root,
				latNow,
				lonNow,
				provider,
				errorBox,
				query
			);
			if ( ok ) {
				recordInteractiveMutation( root );
				if ( query === '' ) {
					persistStoredPlace( root, '' );
				}
			}
		} finally {
			if ( submitBtn ) {
				submitBtn.disabled = false;
			}
		}
	} );
}

async function fetchWeather(
	root,
	lat,
	lon,
	provider,
	errorBox,
	locationQuery = ''
) {
	if ( ! window.forwpWeather ) {
		removeJsonLd( root );
		if ( errorBox ) {
			errorBox.hidden = false;
			errorBox.textContent =
				'Weather configuration is unavailable.';
		}
		root.setAttribute( 'aria-busy', 'false' );
		return false;
	}

	const q =
		typeof locationQuery === 'string'
			? locationQuery.trim()
			: '';

	if ( q === '' ) {
		const latNum = parseFloat( lat );
		const lonNum = parseFloat( lon );
		if (
			! Number.isFinite( latNum ) ||
			! Number.isFinite( lonNum ) ||
			latNum < -90 ||
			latNum > 90 ||
			lonNum < -180 ||
			lonNum > 180
		) {
			removeJsonLd( root );
			if ( errorBox ) {
				errorBox.hidden = false;
				errorBox.textContent =
					'Invalid coordinates after geolocation fallback.';
			}
			root.setAttribute( 'aria-busy', 'false' );
			return false;
		}
	}

	const body = new window.FormData();
	body.append( 'action', window.forwpWeather.action );
	body.append( 'nonce', window.forwpWeather.nonce );
	body.append( 'lat', lat ?? '' );
	body.append( 'lon', lon ?? '' );
	body.append( 'provider', provider );
	if ( q !== '' ) {
		body.append( 'location', q );
	}

	try {
		const response = await window.fetch( window.forwpWeather.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body,
		} );
		const json = await response.json();

		if ( ! json || ! json.success ) {
			throw new Error(
				( json && json.data && json.data.message ) ||
					'Weather request failed.'
			);
		}

		applyPayload( root, json.data );

		if ( q !== '' ) {
			persistStoredPlace( root, q );
		}

		return true;
	} catch ( err ) {
		removeJsonLd( root );
		if ( errorBox ) {
			errorBox.hidden = false;
			errorBox.textContent = err.message || 'Weather request failed.';
		}
		root.setAttribute( 'aria-busy', 'false' );
		return false;
	}
}

function attachDeferredGeoButton( root, provider, errorBox ) {
	const btn = root.querySelector( '.forwp-weather__geo-button' );
	const geoBar = root.querySelector( '.forwp-weather__geo-bar' );
	if ( ! btn ) {
		return;
	}

	btn.addEventListener( 'click', async () => {
		if ( ! enforceCooldownOrShowError( root, errorBox ) ) {
			return;
		}

		btn.disabled = true;
		root.setAttribute( 'aria-busy', 'true' );
		if ( errorBox ) {
			errorBox.hidden = true;
			errorBox.textContent = '';
		}
		let clickLat = root.getAttribute( 'data-lat' );
		let clickLon = root.getAttribute( 'data-lon' );
		const resolved = await tryResolveBrowserGeo(
			root,
			clickLat,
			clickLon
		);
		clickLat = resolved.lat;
		clickLon = resolved.lon;
		const ok = await fetchWeather(
			root,
			clickLat,
			clickLon,
			provider,
			errorBox,
			''
		);
		if ( ok ) {
			recordInteractiveMutation( root );
			persistStoredPlace( root, '' );
			const input = root.querySelector(
				'.forwp-weather__search-input'
			);
			if ( input ) {
				input.value = '';
			}
		}
		if ( geoBar ) {
			geoBar.hidden = true;
		}
		btn.disabled = false;
	} );
}

async function hydrate( root ) {
	clearCooldownTicker( root );

	let lat = root.getAttribute( 'data-lat' );
	let lon = root.getAttribute( 'data-lon' );
	const provider =
		root.getAttribute( 'data-provider' ) || 'openweathermap';
	const useBrowserGeo =
		root.getAttribute( 'data-browser-geo' ) === '1';
	const geoTrigger =
		root.getAttribute( 'data-browser-geo-trigger' ) || 'auto';
	const errorBox = root.querySelector( '.forwp-weather__error' );

	setupLocationSearch( root, provider, errorBox );

	const savedPlace = readStoredPlace( root );
	const hasSearchForm = !! root.querySelector(
		'.forwp-weather__search'
	);
	const useSavedPlaceFirst =
		hasSearchForm &&
		savedPlace !== '' &&
		provider === 'openweathermap';

	if ( useSavedPlaceFirst ) {
		root.setAttribute( 'aria-busy', 'true' );
		await fetchWeather(
			root,
			lat,
			lon,
			provider,
			errorBox,
			savedPlace
		);
		if ( useBrowserGeo && geoTrigger === 'button' ) {
			attachDeferredGeoButton( root, provider, errorBox );
		}
		return;
	}

	if ( useBrowserGeo && geoTrigger === 'button' ) {
		const btn = root.querySelector( '.forwp-weather__geo-button' );
		if ( btn ) {
			attachDeferredGeoButton( root, provider, errorBox );
			return;
		}
	}

	if ( useBrowserGeo ) {
		const resolved = await tryResolveBrowserGeo( root, lat, lon );
		lat = resolved.lat;
		lon = resolved.lon;
	}

	await fetchWeather( root, lat, lon, provider, errorBox, '' );
}

domReady( () => {
	document
		.querySelectorAll( '[data-forwp-weather]' )
		.forEach( ( root ) => hydrate( root ) );
} );
