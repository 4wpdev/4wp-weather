/**
 * Editor UI.
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo } from '@wordpress/element';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	BaseControl,
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { ParamCollapsible } from './param-collapsible';
import { TypeSwitcher } from './type-switcher';
import { FieldLabelPreview } from './field-label';
import { CustomIconPicker } from './custom-icon-picker';
import { IconPicker } from './icon-picker';
import {
	PRESENTATION_MODES,
	TYPE_OPTIONS,
	WEATHER_FIELDS,
	resolveFieldRow,
} from './fields';
import {
	applyWidgetPreset,
	getPresetAttributes,
	getTemplatesConfig,
	LAYOUTS,
	needsPresetFieldPresentation,
	STYLES,
	withPresetFieldPresentation,
} from './templates';

function updateFieldPresentation( fieldKey, patch, fieldPresentation, setAttributes, defaultIcon ) {
	const current = resolveFieldRow(
		fieldPresentation,
		fieldKey,
		defaultIcon
	);

	setAttributes( {
		fieldPresentation: {
			...( fieldPresentation || {} ),
			[ fieldKey ]: {
				...current,
				...patch,
			},
		},
	} );
}

export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		latitude,
		longitude,
		useBrowserGeolocation = true,
		browserGeoTrigger = 'auto',
		showLocationSearch = false,
		provider,
		widgetTemplate = LAYOUTS.ADVANCED,
		widgetStyle = STYLES.DARK,
	} = attributes;

	const displayAttributes = useMemo(
		() => withPresetFieldPresentation( attributes ),
		[ attributes ]
	);
	const fieldPresentation = displayAttributes.fieldPresentation || {};

	useEffect( () => {
		if ( ! needsPresetFieldPresentation( attributes.fieldPresentation ) ) {
			return;
		}

		const preset = getPresetAttributes( widgetTemplate, widgetStyle );
		if ( preset ) {
			setAttributes( preset );
		}
	}, [
		attributes.fieldPresentation,
		widgetTemplate,
		widgetStyle,
		setAttributes,
	] );

	const templatesConfig = getTemplatesConfig();
	const layoutOptions =
		templatesConfig?.layouts?.map( ( item ) => ( {
			label: item.label,
			shortLabel: item.label,
			value: item.slug,
		} ) ) || [
			{
				label: __( 'Small', '4wp-weather' ),
				shortLabel: __( 'Small', '4wp-weather' ),
				value: LAYOUTS.SMALL,
			},
			{
				label: __( 'Compact', '4wp-weather' ),
				shortLabel: __( 'Compact', '4wp-weather' ),
				value: LAYOUTS.COMPACT,
			},
			{
				label: __( 'Advanced', '4wp-weather' ),
				shortLabel: __( 'Advanced', '4wp-weather' ),
				value: LAYOUTS.ADVANCED,
			},
		];
	const styleOptions =
		templatesConfig?.styles?.map( ( item ) => ( {
			label: item.label,
			shortLabel: item.label,
			value: item.slug,
		} ) ) || [
			{
				label: __( 'Dark', '4wp-weather' ),
				shortLabel: __( 'Dark', '4wp-weather' ),
				value: STYLES.DARK,
			},
			{
				label: __( 'White', '4wp-weather' ),
				shortLabel: __( 'White', '4wp-weather' ),
				value: STYLES.WHITE,
			},
		];

	const providerSlug = provider || 'openweathermap';

	const providerOptions =
		typeof window !== 'undefined' &&
		Array.isArray( window.forwpWeatherProviders )
			? window.forwpWeatherProviders
			: [
					{
						label: __( 'OpenWeatherMap', '4wp-weather' ),
						value: 'openweathermap',
						disabled: false,
					},
			  ];

	const blockProps = useBlockProps( {
		className:
			'forwp-weather-block forwp-weather--template-' +
			widgetTemplate +
			' forwp-weather--style-' +
			widgetStyle,
	} );

	const geoButtonMode =
		!! useBrowserGeolocation && browserGeoTrigger === 'button';

	const visibilityRows = WEATHER_FIELDS.filter(
		( field ) => !! displayAttributes[ field.showAttr ]
	).map( ( field ) => ( {
		...field,
		presentation: resolveFieldRow(
			fieldPresentation,
			field.key,
			field.defaultIcon
		),
	} ) );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Widget template', '4wp-weather' ) }
					initialOpen={ false }
				>
					<TypeSwitcher
						label={ __( 'Layout', '4wp-weather' ) }
						value={ widgetTemplate }
						options={ layoutOptions }
						onChange={ ( value ) =>
							applyWidgetPreset(
								value,
								widgetStyle,
								setAttributes
							)
						}
					/>
					<TypeSwitcher
						label={ __( 'Style', '4wp-weather' ) }
						value={ widgetStyle }
						options={ styleOptions }
						onChange={ ( value ) =>
							applyWidgetPreset(
								widgetTemplate,
								value,
								setAttributes
							)
						}
					/>
					<p className="forwp-weather-inspector__help">
						{ __(
							'Layout sets which parameters are shown and their default icons. Style applies the card palette (Dark or White).',
							'4wp-weather'
						) }
					</p>
				</PanelBody>
				<PanelBody
					title={ __( 'Weather Location', '4wp-weather' ) }
					initialOpen={ false }
				>
					<p className="forwp-weather-inspector__heading">
						{ __( 'Weather provider', '4wp-weather' ) }
					</p>
					<SelectControl
						label={ __( 'Provider', '4wp-weather' ) }
						value={ providerSlug }
						options={ providerOptions.map( ( item ) => ( {
							label: item.label,
							value: item.value,
							disabled: !! item.disabled,
						} ) ) }
						onChange={ ( value ) =>
							setAttributes( { provider: value } )
						}
						help={ __(
							'Implemented providers load live data; planned rows are architecture placeholders.',
							'4wp-weather'
						) }
					/>

					<p className="forwp-weather-inspector__heading">
						{ __( 'Weather location', '4wp-weather' ) }
					</p>
					<ToggleControl
						label={ __(
							'Use visitor location (browser)',
							'4wp-weather'
						) }
						checked={ !! useBrowserGeolocation }
						onChange={ ( v ) =>
							setAttributes( {
								useBrowserGeolocation: v,
							} )
						}
						help={ __(
							'Uses the device location when allowed. If denied or unavailable, coordinates below are used. HTTPS or localhost is usually required.',
							'4wp-weather'
						) }
					/>
					{ !! useBrowserGeolocation && (
						<SelectControl
							label={ __( 'Geolocation prompt', '4wp-weather' ) }
							value={
								browserGeoTrigger === 'button'
									? 'button'
									: 'auto'
							}
							options={ [
								{
									label: __(
										'Automatic — on page load',
										'4wp-weather'
									),
									value: 'auto',
								},
								{
									label: __(
										'Button — visitor taps to allow',
										'4wp-weather'
									),
									value: 'button',
								},
							] }
							onChange={ ( value ) =>
								setAttributes( {
									browserGeoTrigger: value,
								} )
							}
							help={ __(
								'Automatic asks as soon as the widget loads. Button waits for a tap so visitors explicitly choose to share location.',
								'4wp-weather'
							) }
						/>
					) }
					<ToggleControl
						label={ __(
							'Location search (front end)',
							'4wp-weather'
						) }
						checked={ !! showLocationSearch }
						onChange={ ( v ) =>
							setAttributes( { showLocationSearch: v } )
						}
						help={ __(
							'Shows a search field so visitors can type a city or place name (OpenWeatherMap). The widget still loads default coordinates first unless geolocation is deferred.',
							'4wp-weather'
						) }
					/>
					<TextControl
						label={ __( 'Latitude', '4wp-weather' ) }
						type="number"
						step="any"
						value={ latitude }
						onChange={ ( v ) =>
							setAttributes( {
								latitude: parseFloat( v ) || 0,
							} )
						}
						help={ __(
							'Decimal degrees (–90 to 90). Used when browser geolocation is off or fails.',
							'4wp-weather'
						) }
					/>
					<TextControl
						label={ __( 'Longitude', '4wp-weather' ) }
						type="number"
						step="any"
						value={ longitude }
						onChange={ ( v ) =>
							setAttributes( {
								longitude: parseFloat( v ) || 0,
							} )
						}
						help={ __(
							'Decimal degrees (–180 to 180). Used when browser geolocation is off or fails.',
							'4wp-weather'
						) }
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Weather Parameters', '4wp-weather' ) }
					initialOpen={ false }
				>
					{ WEATHER_FIELDS.map( ( field ) => {
						const isVisible = !! displayAttributes[ field.showAttr ];
						const row = resolveFieldRow(
							fieldPresentation,
							field.key,
							field.defaultIcon
						);
						const showPresetIcon =
							row.mode === PRESENTATION_MODES.ICON ||
							row.mode === PRESENTATION_MODES.ICON_TEXT;
						const showCustomSvg =
							row.mode === PRESENTATION_MODES.CUSTOM_ICON;

						return (
							<ParamCollapsible
								key={ field.key }
								title={ field.label }
							>
								<ToggleControl
									label={ __(
										'Show in widget',
										'4wp-weather'
									) }
									checked={ isVisible }
									onChange={ ( value ) =>
										setAttributes( {
											[ field.showAttr ]: value,
										} )
									}
								/>
								{ isVisible && (
									<div className="forwp-weather-param__settings">
										<TypeSwitcher
											label={ __(
												'Type',
												'4wp-weather'
											) }
											value={ row.mode }
											options={ TYPE_OPTIONS }
											onChange={ ( value ) =>
												updateFieldPresentation(
													field.key,
													{ mode: value },
													fieldPresentation,
													setAttributes,
													field.defaultIcon
												)
											}
										/>
										{ showPresetIcon && (
											<BaseControl
												label={ __(
													'Icon',
													'4wp-weather'
												) }
												className="forwp-weather-icon-picker-control"
											>
												<IconPicker
													value={ row.icon }
													style={ row }
													onChange={ ( value ) =>
														updateFieldPresentation(
															field.key,
															{
																icon: value,
															},
															fieldPresentation,
															setAttributes,
															field.defaultIcon
														)
													}
												/>
											</BaseControl>
										) }
										{ showCustomSvg && (
											<CustomIconPicker
												presentation={ row }
												onChange={ ( patch ) =>
													updateFieldPresentation(
														field.key,
														patch,
														fieldPresentation,
														setAttributes,
														field.defaultIcon
													)
												}
											/>
										) }
									</div>
								) }
							</ParamCollapsible>
						);
					} ) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="forwp-weather__card">
					<p className="forwp-weather__status screen-reader-text">
						{ __(
							'Editor preview — values load on the front end.',
							'4wp-weather'
						) }
					</p>
					{ geoButtonMode && (
						<div className="forwp-weather__geo-bar">
							<button
								type="button"
								className="forwp-weather__geo-button wp-element-button"
								disabled
							>
								{ __( 'Use my location', '4wp-weather' ) }
							</button>
						</div>
					) }
					{ !! showLocationSearch && (
						<form
							className="forwp-weather__search"
							onSubmit={ ( event ) =>
								event.preventDefault()
							}
						>
							<label
								className="screen-reader-text"
								htmlFor={ `forwp-weather-editor-search-${ clientId }` }
							>
								{ __( 'City or place', '4wp-weather' ) }
							</label>
							<input
								id={ `forwp-weather-editor-search-${ clientId }` }
								className="forwp-weather__search-input"
								type="search"
								disabled
								autoComplete="off"
								placeholder={ __(
									'City or place…',
									'4wp-weather'
								) }
							/>
							<button
								type="submit"
								className="forwp-weather__search-submit wp-element-button"
								disabled
							>
								{ __( 'Search', '4wp-weather' ) }
							</button>
						</form>
					) }
					<table className="forwp-weather__table">
						<tbody>
							{ visibilityRows.length === 0 ? (
								<tr className="forwp-weather__row forwp-weather__row--empty">
									<td
										colSpan={ 2 }
										className="forwp-weather__value"
									>
										{ __(
											'Enable at least one parameter under “Weather Parameters”.',
											'4wp-weather'
										) }
									</td>
								</tr>
							) : (
								visibilityRows.map( ( row ) => (
									<tr
										key={ row.key }
										className={ `forwp-weather__row forwp-weather__row--${ row.key }` }
									>
										<th
											scope="row"
											className={
												`forwp-weather__label forwp-weather__label--${ row.presentation.mode }` +
												( row.presentation.labelText?.trim()
													? ' forwp-weather__label--custom'
													: '' )
											}
										>
											<FieldLabelPreview
												label={ row.label }
												presentation={
													row.presentation
												}
												onLabelChange={ ( labelText ) =>
													updateFieldPresentation(
														row.key,
														{ labelText },
														fieldPresentation,
														setAttributes,
														row.defaultIcon
													)
												}
											/>
										</th>
										<td className="forwp-weather__value">
											—
										</td>
									</tr>
								) )
							) }
						</tbody>
					</table>
					<p
						className="forwp-weather__editor-note"
						aria-hidden="true"
					>
						{ __( 'Fallback coordinates:', '4wp-weather' ) }{ ' ' }
						{ latitude }, { longitude }
					</p>
				</div>
			</div>
		</>
	);
}
