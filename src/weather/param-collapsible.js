/**
 * Collapsible param row — avoids nested PanelBody (breaks block inspector).
 */
import { useState } from '@wordpress/element';
import { Button } from '@wordpress/components';

/**
 * @param {Object}   props
 * @param {string}   props.title
 * @param {boolean}  [props.defaultOpen]
 * @param {import('react').ReactNode} props.children
 */
export function ParamCollapsible( {
	title,
	defaultOpen = false,
	children,
} ) {
	const [ isOpen, setIsOpen ] = useState( defaultOpen );
	const panelId = `forwp-weather-param-${ title.replace( /\s+/g, '-' ).toLowerCase() }`;

	return (
		<div className="forwp-weather-param-panel">
			<Button
				className="forwp-weather-param-panel__toggle"
				onClick={ () => setIsOpen( ( open ) => ! open ) }
				aria-expanded={ isOpen }
				aria-controls={ panelId }
			>
				<span className="forwp-weather-param-panel__title">
					{ title }
				</span>
				<span
					className={
						'forwp-weather-param-panel__chevron' +
						( isOpen ? ' is-open' : '' )
					}
					aria-hidden="true"
				/>
			</Button>
			{ isOpen && (
				<div
					id={ panelId }
					className="forwp-weather-param-panel__content"
				>
					{ children }
				</div>
			) }
		</div>
	);
}
