/**
 * Native-style compact color row + single ⋮ popover (no nested dropdowns).
 */
import { __ } from '@wordpress/i18n';
import { useSetting } from '@wordpress/block-editor';
import {
	Button,
	ColorIndicator,
	ColorPalette,
	Dropdown,
} from '@wordpress/components';

/**
 * @param {Object}   props
 * @param {Array}    props.settings { key, label, value, onChange }[]
 */
export function LabelColorControls( { settings } ) {
	const colors = useSetting( 'color.palette' ) || [];

	if ( ! settings.length ) {
		return null;
	}

	return (
		<div className="forwp-weather-label-colors">
			<div className="forwp-weather-label-colors__header">
				<span className="forwp-weather-label-colors__heading">
					{ __( 'Color', '4wp-weather' ) }
				</span>
				<Dropdown
					popoverProps={ {
						placement: 'left-start',
						className: 'forwp-weather-colors-menu',
					} }
					renderToggle={ ( { isOpen, onToggle } ) => (
						<Button
							size="small"
							onClick={ onToggle }
							aria-expanded={ isOpen }
							label={ __( 'Color options', '4wp-weather' ) }
							className="forwp-weather-label-colors__menu"
						>
							<span
								className="forwp-weather-label-colors__menu-icon"
								aria-hidden="true"
							>
								⋮
							</span>
						</Button>
					) }
					renderContent={ () => (
						<div className="forwp-weather-colors-popover">
							{ settings.map( ( setting ) => (
								<div
									key={ setting.key }
									className="forwp-weather-colors-popover__group"
								>
									<p className="forwp-weather-colors-popover__label">
										{ setting.label }
									</p>
									<ColorPalette
										colors={ colors }
										value={ setting.value || undefined }
										onChange={ ( color ) =>
											setting.onChange( color || '' )
										}
										clearable
									/>
								</div>
							) ) }
						</div>
					) }
				/>
			</div>
			<div className="forwp-weather-label-colors__items">
				{ settings.map( ( setting ) => (
					<div
						key={ setting.key }
						className="forwp-weather-label-colors__item"
					>
						<span className="forwp-weather-label-colors__item-label">
							{ setting.label }
						</span>
						<ColorIndicator
							colorValue={ setting.value || undefined }
						/>
					</div>
				) ) }
			</div>
		</div>
	);
}
