/**
 * Per-field label colors + icon dimensions — native block-inspector patterns.
 */
import { __ } from '@wordpress/i18n';
import { modeUsesIconStyles, PRESENTATION_MODES } from './fields';
import { IconDimensionsControl } from './icon-dimensions-control';
import { LabelColorControls } from './label-color-controls';

// Re-export for icon-picker preview.
export { buildIconStyle } from './icon-style-utils';

/**
 * @param {Object}   props
 * @param {Object}   props.values
 * @param {string}   props.mode     Presentation mode.
 * @param {Function} props.onChange Patch callback.
 */
export function IconStyleControls( { values, mode, onChange } ) {
	const showIconFields = modeUsesIconStyles( mode );

	const showTextColor =
		mode === PRESENTATION_MODES.TEXT ||
		mode === PRESENTATION_MODES.ICON_TEXT;

	const colorSettings = [];

	if ( showTextColor ) {
		colorSettings.push( {
			key: 'labelColor',
			label: __( 'Text', '4wp-weather' ),
			value: values.labelColor,
			onChange: ( color ) => onChange( { labelColor: color } ),
		} );
	}

	if ( showIconFields ) {
		colorSettings.push( {
			key: 'iconColor',
			label: __( 'Icon', '4wp-weather' ),
			value: values.iconColor,
			onChange: ( color ) => onChange( { iconColor: color } ),
		} );
	}

	colorSettings.push( {
		key: 'iconBackground',
		label: __( 'Background', '4wp-weather' ),
		value: values.iconBackground,
		onChange: ( color ) => onChange( { iconBackground: color } ),
	} );

	return (
		<div className="forwp-weather-label-style">
			<LabelColorControls settings={ colorSettings } />
			{ showIconFields && (
				<IconDimensionsControl
					values={ values }
					onChange={ onChange }
				/>
			) }
		</div>
	);
}
