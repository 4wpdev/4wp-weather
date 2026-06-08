/**
 * Builtin weather field icons (shared editor + preview).
 */
import { __ } from '@wordpress/i18n';

export const BUILTIN_ICON_SVGS = {
	'map-pin':
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 21s6-5.2 6-10a6 6 0 1 0-12 0c0 4.8 6 10 6 10Z"/><circle cx="12" cy="11" r="2.5"/></svg>',
	thermometer:
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14 14.8V5a2 2 0 1 0-4 0v9.8a4 4 0 1 0 4 0Z"/><line x1="10" y1="9" x2="10" y2="15"/></svg>',
	'thermometer-sun':
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14 14.8V5a2 2 0 1 0-4 0v9.8a4 4 0 1 0 4 0Z"/><circle cx="18" cy="6" r="2"/><line x1="18" y1="2" x2="18" y2="3"/><line x1="22" y1="6" x2="21" y2="6"/><line x1="20.2" y1="3.8" x2="19.5" y2="4.5"/></svg>',
	'cloud-sun':
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3v2"/><path d="m4.9 4.9 1.4 1.4"/><path d="M20 12h2"/><path d="m18.4 6.3-1.4 1.4"/><circle cx="12" cy="7" r="3"/><path d="M7 18a4 4 0 0 1 0-8 4.8 4.8 0 0 1 9.2 1.5A3.5 3.5 0 0 1 18.5 18Z"/></svg>',
	droplets:
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 22a6 6 0 0 0 6-10c0-4-6-10-6-10S6 8 6 12a6 6 0 0 0 6 10Z"/></svg>',
	gauge:
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m12 14 4-6"/><path d="M12 6v2"/><circle cx="12" cy="14" r="8"/></svg>',
	wind: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2"/><path d="M9.6 4.6A2 2 0 1 1 11 8H2"/><path d="M12.6 19.4A2 2 0 1 0 14 16H2"/></svg>',
	sunrise:
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 2v4"/><path d="m4.9 10.9 2.8 2.8"/><path d="M2 18h2"/><path d="M20 18h2"/><path d="m16.3 13.7 2.8-2.8"/><path d="M12 10a4 4 0 1 0 0 8 7 7 0 0 1 0-8Z"/><path d="M4 22h16"/></svg>',
	sunset:
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 10V2"/><path d="m4.9 10.9 2.8 2.8"/><path d="M2 18h2"/><path d="M20 18h2"/><path d="m16.3 13.7 2.8-2.8"/><path d="M12 14a4 4 0 1 0 0 8 7 7 0 0 1 0-8Z"/><path d="M4 22h16"/></svg>',
};

export const BUILTIN_ICONS = [
	{ value: 'map-pin', label: __( 'Map pin', '4wp-weather' ) },
	{ value: 'thermometer', label: __( 'Thermometer', '4wp-weather' ) },
	{ value: 'thermometer-sun', label: __( 'Thermometer (sun)', '4wp-weather' ) },
	{ value: 'cloud-sun', label: __( 'Cloud and sun', '4wp-weather' ) },
	{ value: 'droplets', label: __( 'Droplets', '4wp-weather' ) },
	{ value: 'gauge', label: __( 'Gauge', '4wp-weather' ) },
	{ value: 'wind', label: __( 'Wind', '4wp-weather' ) },
	{ value: 'sunrise', label: __( 'Sunrise', '4wp-weather' ) },
	{ value: 'sunset', label: __( 'Sunset', '4wp-weather' ) },
];
