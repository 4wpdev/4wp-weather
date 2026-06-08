/**
 * Field label preview (editor) — mirrors PHP Field_Presentation output.
 */
import { RichText } from '@wordpress/block-editor';
import {
	getFieldLabelText,
	modeShowsEditableLabel,
	PRESENTATION_MODES,
} from './fields';
import { BUILTIN_ICON_SVGS } from './builtin-icons';
import { CustomFieldIcon } from './custom-field-icon';
import {
	buildIconStyle,
	buildLabelInnerStyle,
	buildTextStyle,
} from './icon-style-utils';

function BuiltinFieldIcon( { slug, style } ) {
	const svg = BUILTIN_ICON_SVGS[ slug ];

	if ( ! svg ) {
		return null;
	}

	return (
		<span
			className="forwp-weather__field-icon"
			aria-hidden="true"
			style={ style }
			dangerouslySetInnerHTML={ { __html: svg } }
		/>
	);
}

function EditableLabelText( {
	value,
	placeholder,
	style,
	onChange,
	className = 'forwp-weather__label-text',
} ) {
	return (
		<RichText
			tagName="span"
			className={ `${ className } forwp-weather__label-text--editable` }
			value={ value }
			onChange={ onChange }
			placeholder={ placeholder }
			allowedFormats={ [] }
			style={ style }
		/>
	);
}

/**
 * @param {Object}        props
 * @param {string}        props.label         Default translated label.
 * @param {Object}        props.presentation  Resolved field row.
 * @param {Function|null} props.onLabelChange Save custom label text.
 */
export function FieldLabelPreview( {
	label,
	presentation,
	onLabelChange,
} ) {
	const { mode, icon } = presentation;
	const innerStyle = buildLabelInnerStyle( presentation );
	const textStyle = buildTextStyle( presentation );
	const iconStyle = buildIconStyle( presentation );
	const displayLabel = getFieldLabelText( presentation, label );
	const hasCustomLabel = Boolean( presentation.labelText?.trim() );
	const isEditable =
		typeof onLabelChange === 'function' &&
		modeShowsEditableLabel( mode );

	if ( mode === PRESENTATION_MODES.TEXT ) {
		return (
			<span
				className={
					'forwp-weather__label-inner forwp-weather__label-inner--text' +
					( hasCustomLabel ? ' forwp-weather__label-inner--custom' : '' )
				}
				style={ innerStyle }
			>
				{ isEditable ? (
					<EditableLabelText
						value={ presentation.labelText || '' }
						placeholder={ label }
						style={ textStyle }
						onChange={ onLabelChange }
					/>
				) : (
					<span style={ textStyle }>{ displayLabel }</span>
				) }
			</span>
		);
	}

	if ( mode === PRESENTATION_MODES.ICON ) {
		return (
			<span
				className="forwp-weather__label-inner forwp-weather__label-inner--icon"
				style={ innerStyle }
			>
				<BuiltinFieldIcon slug={ icon } style={ iconStyle } />
				<span className="screen-reader-text">{ label }</span>
			</span>
		);
	}

	if ( mode === PRESENTATION_MODES.CUSTOM_ICON ) {
		return (
			<span
				className="forwp-weather__label-inner forwp-weather__label-inner--custom-icon"
				style={ innerStyle }
			>
				<CustomFieldIcon
					presentation={ presentation }
					style={ iconStyle }
				/>
				<span className="screen-reader-text">{ label }</span>
			</span>
		);
	}

	return (
		<span
			className="forwp-weather__label-inner forwp-weather__label-inner--icon-text"
			style={ innerStyle }
		>
			<BuiltinFieldIcon slug={ icon } style={ iconStyle } />
			{ isEditable ? (
				<EditableLabelText
					value={ presentation.labelText || '' }
					placeholder={ label }
					style={ textStyle }
					onChange={ onLabelChange }
					className={
						'forwp-weather__label-text' +
						( hasCustomLabel
							? ' forwp-weather__label-text--custom'
							: '' )
					}
				/>
			) : (
				<span
					className={
						'forwp-weather__label-text' +
						( hasCustomLabel
							? ' forwp-weather__label-text--custom'
							: '' )
					}
					style={ textStyle }
				>
					{ displayLabel }
				</span>
			) }
		</span>
	);
}
