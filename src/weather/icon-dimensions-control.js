/**
 * Native-style Dimensions control (+ → Padding → BoxControl).
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Button,
	Dropdown,
	__experimentalBoxControl as BoxControl,
} from '@wordpress/components';
import {
	getIconPaddingValues,
	patchIconPadding,
} from './icon-style-utils';

/**
 * @param {Object}   props
 * @param {Object}   props.values
 * @param {Function} props.onChange
 */
export function IconDimensionsControl( { values, onChange } ) {
	const [ panel, setPanel ] = useState( 'menu' );
	const paddingValues = getIconPaddingValues( values );
	const hasPadding = Object.values( paddingValues ).some( Boolean );

	const resetPanel = () => setPanel( 'menu' );

	return (
		<div className="forwp-weather-dimensions">
			<div className="forwp-weather-dimensions__header">
				<span className="forwp-weather-dimensions__heading">
					{ __( 'Dimensions', '4wp-weather' ) }
				</span>
				<Dropdown
					popoverProps={ {
						placement: 'left-start',
						className: 'forwp-weather-dimensions__popover-wrap',
						onClose: resetPanel,
					} }
					renderToggle={ ( { isOpen, onToggle } ) => (
						<Button
							size="small"
							variant="primary"
							onClick={ onToggle }
							aria-expanded={ isOpen }
							label={ __( 'Add dimensions', '4wp-weather' ) }
							className="forwp-weather-dimensions__add"
						>
							+
						</Button>
					) }
					renderContent={ () => (
						<div className="forwp-weather-dimensions__popover">
							{ panel === 'menu' ? (
								<>
									<p className="forwp-weather-dimensions__popover-title">
										{ __( 'Dimensions', '4wp-weather' ) }
									</p>
									<Button
										variant="secondary"
										className="forwp-weather-dimensions__option"
										onClick={ () => setPanel( 'padding' ) }
									>
										{ __( 'Padding', '4wp-weather' ) }
									</Button>
								</>
							) : (
								<div className="forwp-weather-dimensions__padding">
									<Button
										variant="link"
										className="forwp-weather-dimensions__back"
										onClick={ resetPanel }
									>
										{ __( 'Back', '4wp-weather' ) }
									</Button>
									<BoxControl
										__next40pxDefaultSize
										label={ __( 'Padding', '4wp-weather' ) }
										values={ paddingValues }
										onChange={ ( nextValues ) =>
											onChange(
												patchIconPadding( nextValues )
											)
										}
										allowReset
									/>
									<Button
										variant="link"
										className="forwp-weather-dimensions__reset"
										onClick={ () =>
											onChange( patchIconPadding( {} ) )
										}
										disabled={ ! hasPadding }
									>
										{ __( 'Reset all', '4wp-weather' ) }
									</Button>
								</div>
							) }
						</div>
					) }
				/>
			</div>
		</div>
	);
}
