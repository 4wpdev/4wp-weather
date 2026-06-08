/**
 * Widget layout + style presets (mirrors PHP Widget_Templates).
 */

export const LAYOUTS = {
	SMALL: 'small',
	COMPACT: 'compact',
	ADVANCED: 'advanced',
};

export const STYLES = {
	DARK: 'dark',
	WHITE: 'white',
};

/**
 * @return {Object|null}
 */
export function getTemplatesConfig() {
	if (
		typeof window === 'undefined' ||
		! window.forwpWeatherTemplates ||
		typeof window.forwpWeatherTemplates !== 'object'
	) {
		return null;
	}
	return window.forwpWeatherTemplates;
}

/**
 * @param {string} layout Layout slug.
 * @param {string} style  Style slug.
 * @return {Object|null}
 */
export function getPresetAttributes( layout, style ) {
	const config = getTemplatesConfig();
	if ( ! config || ! config.presets ) {
		return null;
	}
	const key = `${ layout }:${ style }`;
	return config.presets[ key ] || null;
}

/**
 * @param {Object|null|undefined} fieldPresentation Block fieldPresentation attr.
 * @return {boolean}
 */
export function needsPresetFieldPresentation( fieldPresentation ) {
	return (
		! fieldPresentation ||
		typeof fieldPresentation !== 'object' ||
		Object.keys( fieldPresentation ).length === 0
	);
}

/**
 * @param {Object} row Saved presentation row.
 * @return {boolean}
 */
function fieldPresentationRowIsEmpty( row ) {
	if ( ! row || typeof row !== 'object' ) {
		return true;
	}

	const hasMode = typeof row.mode === 'string' && row.mode !== '';
	const hasIcon = typeof row.icon === 'string' && row.icon !== '';
	const hasLabel =
		typeof row.labelText === 'string' && row.labelText.trim() !== '';
	const hasCustomIcon =
		( Number.isFinite( Number( row.customIconId ) ) &&
			Number( row.customIconId ) > 0 ) ||
		( typeof row.customSvg === 'string' && row.customSvg !== '' );

	return ! hasMode && ! hasIcon && ! hasLabel && ! hasCustomIcon;
}

/**
 * Merge layout preset presentation; saved per-field values win.
 *
 * @param {Object} attributes Block attributes.
 * @return {Object}
 */
export function withPresetFieldPresentation( attributes ) {
	const preset = getPresetAttributes(
		attributes.widgetTemplate || LAYOUTS.ADVANCED,
		attributes.widgetStyle || STYLES.DARK
	);

	if ( ! preset?.fieldPresentation ) {
		return attributes;
	}

	const current =
		attributes.fieldPresentation &&
		typeof attributes.fieldPresentation === 'object'
			? attributes.fieldPresentation
			: {};

	const merged = { ...current };

	Object.entries( preset.fieldPresentation ).forEach(
		( [ fieldKey, presetRow ] ) => {
			const savedRow =
				merged[ fieldKey ] && typeof merged[ fieldKey ] === 'object'
					? merged[ fieldKey ]
					: {};

			if ( fieldPresentationRowIsEmpty( savedRow ) ) {
				merged[ fieldKey ] = presetRow;
				return;
			}

			merged[ fieldKey ] = {
				...presetRow,
				...savedRow,
			};
		}
	);

	return {
		...attributes,
		fieldPresentation: merged,
	};
}

/**
 * Apply a widget template preset to block attributes.
 *
 * @param {string}   layout        Layout slug.
 * @param {string}   style         Style slug.
 * @param {Function} setAttributes Block setter.
 */
export function applyWidgetPreset( layout, style, setAttributes ) {
	const preset = getPresetAttributes( layout, style );
	if ( ! preset || typeof preset !== 'object' ) {
		setAttributes( {
			widgetTemplate: layout,
			widgetStyle: style,
		} );
		return;
	}
	setAttributes( preset );
}
