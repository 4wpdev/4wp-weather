/**
 * Visual builtin icon picker (4-column grid).
 */
import { __ } from '@wordpress/i18n';
import { BUILTIN_ICON_SVGS, BUILTIN_ICONS } from './builtin-icons';
import { buildIconStyle, buildLabelInnerStyle } from './icon-style-utils';

function IconPreview( { slug, style, className } ) {
	const svg = BUILTIN_ICON_SVGS[ slug ];
	if ( ! svg ) {
		return null;
	}

	return (
		<span
			className={ className }
			aria-hidden="true"
			style={ style }
			dangerouslySetInnerHTML={ { __html: svg } }
		/>
	);
}

/**
 * @param {Object}   props
 * @param {string}   props.value    Selected icon slug.
 * @param {Function} props.onChange Callback with slug.
 * @param {Object}   [props.style]  Optional preview styles (color, bg, padding).
 */
export function IconPicker( { value, onChange, style = {} } ) {
	const selected = BUILTIN_ICONS.find( ( item ) => item.value === value );
	const previewStyle = {
		...buildLabelInnerStyle( style ),
		...buildIconStyle( style ),
	};

	return (
		<div className="forwp-weather-icon-picker">
			<div
				className="forwp-weather-icon-picker__grid"
				role="listbox"
				aria-label={ __( 'Icon', '4wp-weather' ) }
			>
				{ BUILTIN_ICONS.map( ( item ) => {
					const isSelected = item.value === value;

					return (
						<button
							key={ item.value }
							type="button"
							role="option"
							aria-selected={ isSelected }
							aria-label={ item.label }
							className={
								'forwp-weather-icon-picker__option' +
								( isSelected
									? ' is-selected'
									: '' )
							}
							onClick={ () => onChange( item.value ) }
						>
							<IconPreview
								slug={ item.value }
								style={ isSelected ? previewStyle : undefined }
								className="forwp-weather-icon-picker__option-svg"
							/>
						</button>
					);
				} ) }
			</div>
			{ selected && (
				<p className="forwp-weather-icon-picker__selected">
					{ selected.label }
				</p>
			) }
		</div>
	);
}
