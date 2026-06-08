/**
 * Segmented control without experimental ToggleGroup (stable in all WP 6.4+ builds).
 */
import { BaseControl, Button } from '@wordpress/components';

/**
 * @param {Object}   props
 * @param {string}   props.label
 * @param {string}   props.value
 * @param {Array}    props.options { value, shortLabel, label }[]
 * @param {Function} props.onChange
 * @param {string}   [props.className]
 */
export function TypeSwitcher( {
	label,
	value,
	options,
	onChange,
	className = '',
} ) {
	return (
		<BaseControl
			label={ label }
			className={ 'forwp-weather-type-switcher-control ' + className }
		>
			<div
				className="forwp-weather-type-switcher"
				role="group"
				aria-label={ label }
			>
				{ options.map( ( option ) => (
					<Button
						key={ option.value }
						variant={
							value === option.value ? 'primary' : 'secondary'
						}
						className="forwp-weather-type-switcher__btn"
						onClick={ () => onChange( option.value ) }
						aria-pressed={ value === option.value }
						aria-label={ option.label || option.shortLabel }
					>
						{ option.shortLabel }
					</Button>
				) ) }
			</div>
		</BaseControl>
	);
}
