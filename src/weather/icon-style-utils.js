/**
 * Shared label/icon inline style helpers (editor preview).
 */

const PADDING_KEYS = [
	'iconPaddingTop',
	'iconPaddingRight',
	'iconPaddingBottom',
	'iconPaddingLeft',
];

/**
 * @param {Object} presentation Field presentation row.
 * @return {{top?: string, right?: string, bottom?: string, left?: string}}
 */
export function getIconPaddingValues( presentation ) {
	const hasSides = PADDING_KEYS.some( ( key ) => !! presentation[ key ] );

	if ( hasSides ) {
		return {
			top: presentation.iconPaddingTop || undefined,
			right: presentation.iconPaddingRight || undefined,
			bottom: presentation.iconPaddingBottom || undefined,
			left: presentation.iconPaddingLeft || undefined,
		};
	}

	if ( presentation.iconPadding ) {
		return {
			top: presentation.iconPadding,
			right: presentation.iconPadding,
			bottom: presentation.iconPadding,
			left: presentation.iconPadding,
		};
	}

	return {};
}

/**
 * Background on the label wrapper (text + icon).
 *
 * @param {Object} presentation Field presentation row.
 * @return {Object}
 */
export function buildLabelInnerStyle( presentation ) {
	const style = {};

	if ( presentation.iconBackground ) {
		style.backgroundColor = presentation.iconBackground;
	}

	return style;
}

/**
 * Text color for label copy.
 *
 * @param {Object} presentation Field presentation row.
 * @return {Object}
 */
export function buildTextStyle( presentation ) {
	const style = {};

	if ( presentation.labelColor ) {
		style.color = presentation.labelColor;
	}

	return style;
}

/**
 * Icon box: optional per-field color override + padding.
 * When iconColor is empty, icons inherit block text color via CSS.
 *
 * @param {Object} presentation Field presentation row.
 * @return {Object}
 */
export function buildIconStyle( presentation ) {
	const style = {};

	if ( presentation.iconColor ) {
		style.color = presentation.iconColor;
	}

	const padding = getIconPaddingValues( presentation );
	if ( padding.top ) {
		style.paddingTop = padding.top;
	}
	if ( padding.right ) {
		style.paddingRight = padding.right;
	}
	if ( padding.bottom ) {
		style.paddingBottom = padding.bottom;
	}
	if ( padding.left ) {
		style.paddingLeft = padding.left;
	}

	return style;
}

/**
 * @param {{top?: string, right?: string, bottom?: string, left?: string}} values BoxControl values.
 * @return {Object} fieldPresentation patch.
 */
export function patchIconPadding( values ) {
	return {
		iconPaddingTop: values.top || '',
		iconPaddingRight: values.right || '',
		iconPaddingBottom: values.bottom || '',
		iconPaddingLeft: values.left || '',
		iconPadding: '',
	};
}

/**
 * Uniform padding for compact inspector control.
 *
 * @param {Object} presentation Field presentation row.
 * @return {string}
 */
export function getUniformIconPadding( presentation ) {
	const padding = getIconPaddingValues( presentation );
	const sides = [
		padding.top,
		padding.right,
		padding.bottom,
		padding.left,
	].filter( Boolean );

	if ( ! sides.length ) {
		return '';
	}

	const unique = [ ...new Set( sides ) ];
	return unique.length === 1 ? unique[ 0 ] : sides[ 0 ];
}

/**
 * @param {string} value Uniform padding value.
 * @return {Object}
 */
export function patchUniformIconPadding( value ) {
	const normalized = value || '';

	return {
		iconPaddingTop: normalized,
		iconPaddingRight: normalized,
		iconPaddingBottom: normalized,
		iconPaddingLeft: normalized,
		iconPadding: '',
	};
}
