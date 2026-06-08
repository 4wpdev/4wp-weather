/**
 * Shared weather field + icon definitions (editor).
 */
import { __ } from '@wordpress/i18n';
export { BUILTIN_ICONS } from './builtin-icons';

export const PRESENTATION_MODES = {
	TEXT: 'text',
	ICON: 'icon',
	ICON_TEXT: 'icon-text',
	CUSTOM_ICON: 'custom-icon',
};

export const TYPE_OPTIONS = [
	{
		label: __( 'Icon', '4wp-weather' ),
		shortLabel: __( 'Icon', '4wp-weather' ),
		value: PRESENTATION_MODES.ICON,
	},
	{
		label: __( 'Icon + text', '4wp-weather' ),
		shortLabel: __( 'Mix', '4wp-weather' ),
		value: PRESENTATION_MODES.ICON_TEXT,
	},
	{
		label: __( 'Text', '4wp-weather' ),
		shortLabel: __( 'Text', '4wp-weather' ),
		value: PRESENTATION_MODES.TEXT,
	},
	{
		label: __( 'Custom icon', '4wp-weather' ),
		shortLabel: __( 'Custom', '4wp-weather' ),
		value: PRESENTATION_MODES.CUSTOM_ICON,
	},
];

export const WEATHER_FIELDS = [
	{
		key: 'locationName',
		label: __( 'Location', '4wp-weather' ),
		showAttr: 'showLocationName',
		defaultIcon: 'map-pin',
	},
	{
		key: 'temperature',
		label: __( 'Temperature', '4wp-weather' ),
		showAttr: 'showTemperature',
		defaultIcon: 'thermometer',
	},
	{
		key: 'feelsLike',
		label: __( 'Feels like', '4wp-weather' ),
		showAttr: 'showFeelsLike',
		defaultIcon: 'thermometer-sun',
	},
	{
		key: 'condition',
		label: __( 'Condition', '4wp-weather' ),
		showAttr: 'showCondition',
		defaultIcon: 'cloud-sun',
	},
	{
		key: 'humidity',
		label: __( 'Humidity', '4wp-weather' ),
		showAttr: 'showHumidity',
		defaultIcon: 'droplets',
	},
	{
		key: 'pressure',
		label: __( 'Pressure', '4wp-weather' ),
		showAttr: 'showPressure',
		defaultIcon: 'gauge',
	},
	{
		key: 'windSpeed',
		label: __( 'Wind', '4wp-weather' ),
		showAttr: 'showWindSpeed',
		defaultIcon: 'wind',
	},
	{
		key: 'sunrise',
		label: __( 'Sunrise', '4wp-weather' ),
		showAttr: 'showSunrise',
		defaultIcon: 'sunrise',
	},
	{
		key: 'sunset',
		label: __( 'Sunset', '4wp-weather' ),
		showAttr: 'showSunset',
		defaultIcon: 'sunset',
	},
];

const VALID_MODES = Object.values( PRESENTATION_MODES );

/**
 * @param {Object} fieldPresentation Block attribute.
 * @param {string} fieldKey          Field key.
 * @param {string} defaultIcon       Default icon slug.
 * @return {Object} Resolved presentation row.
 */
export function resolveFieldRow( fieldPresentation, fieldKey, defaultIcon ) {
	const row =
		fieldPresentation && typeof fieldPresentation === 'object'
			? fieldPresentation[ fieldKey ]
			: null;

	const mode =
		row && typeof row.mode === 'string' ? row.mode : PRESENTATION_MODES.TEXT;
	const icon =
		row && typeof row.icon === 'string' ? row.icon : defaultIcon;

	return {
		mode: VALID_MODES.includes( mode ) ? mode : PRESENTATION_MODES.TEXT,
		icon,
		labelColor:
			row && typeof row.labelColor === 'string' ? row.labelColor : '',
		iconColor:
			row && typeof row.iconColor === 'string' ? row.iconColor : '',
		iconBackground:
			row && typeof row.iconBackground === 'string'
				? row.iconBackground
				: '',
		iconPadding:
			row && typeof row.iconPadding === 'string' ? row.iconPadding : '',
		iconPaddingTop:
			row && typeof row.iconPaddingTop === 'string'
				? row.iconPaddingTop
				: '',
		iconPaddingRight:
			row && typeof row.iconPaddingRight === 'string'
				? row.iconPaddingRight
				: '',
		iconPaddingBottom:
			row && typeof row.iconPaddingBottom === 'string'
				? row.iconPaddingBottom
				: '',
		iconPaddingLeft:
			row && typeof row.iconPaddingLeft === 'string'
				? row.iconPaddingLeft
				: '',
		customIconId:
			row && Number.isFinite( Number( row.customIconId ) )
				? Math.max( 0, parseInt( row.customIconId, 10 ) )
				: 0,
		customSvg:
			row && typeof row.customSvg === 'string' ? row.customSvg : '',
		labelText:
			row && typeof row.labelText === 'string' ? row.labelText : '',
	};
}

/**
 * @param {Object} presentation Resolved field row.
 * @param {string} defaultLabel Default translated label.
 * @return {string}
 */
export function getFieldLabelText( presentation, defaultLabel ) {
	const custom =
		presentation && typeof presentation.labelText === 'string'
			? presentation.labelText.trim()
			: '';

	return custom || defaultLabel;
}

/**
 * @param {string} mode Presentation mode.
 * @return {boolean}
 */
export function modeShowsEditableLabel( mode ) {
	return (
		mode === PRESENTATION_MODES.TEXT ||
		mode === PRESENTATION_MODES.ICON_TEXT
	);
}

/**
 * @param {string} mode Presentation mode.
 * @return {boolean}
 */
export function modeUsesIconStyles( mode ) {
	return (
		mode === PRESENTATION_MODES.ICON ||
		mode === PRESENTATION_MODES.ICON_TEXT ||
		mode === PRESENTATION_MODES.CUSTOM_ICON
	);
}
