/**
 * Editor UI.
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	PanelRow,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';

export default function Edit( { attributes, setAttributes, clientId } ) {
	const {
		latitude,
		longitude,
		useBrowserGeolocation = true,
		browserGeoTrigger = 'auto',
		showLocationSearch = false,
		provider,
		showLocationName,
		showTemperature,
		showFeelsLike,
		showCondition,
		showHumidity,
		showPressure,
		showWindSpeed,
		showSunrise,
		showSunset,
	} = attributes;

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
		className: 'forwp-weather-block',
	} );

	const geoButtonMode =
		!! useBrowserGeolocation && browserGeoTrigger === 'button';

	const visibilityRows = [
		{
			key: 'locationName',
			show: !! showLocationName,
			label: __( 'Location', '4wp-weather' ),
		},
		{
			key: 'temperature',
			show: !! showTemperature,
			label: __( 'Temperature', '4wp-weather' ),
		},
		{
			key: 'feelsLike',
			show: !! showFeelsLike,
			label: __( 'Feels like', '4wp-weather' ),
		},
		{
			key: 'condition',
			show: !! showCondition,
			label: __( 'Condition', '4wp-weather' ),
		},
		{
			key: 'humidity',
			show: !! showHumidity,
			label: __( 'Humidity', '4wp-weather' ),
		},
		{
			key: 'pressure',
			show: !! showPressure,
			label: __( 'Pressure', '4wp-weather' ),
		},
		{
			key: 'windSpeed',
			show: !! showWindSpeed,
			label: __( 'Wind', '4wp-weather' ),
		},
		{
			key: 'sunrise',
			show: !! showSunrise,
			label: __( 'Sunrise', '4wp-weather' ),
		},
		{
			key: 'sunset',
			show: !! showSunset,
			label: __( 'Sunset', '4wp-weather' ),
		},
	].filter( ( row ) => row.show );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Data provider', '4wp-weather' ) }
					initialOpen={ true }
				>
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
				</PanelBody>
				<PanelBody
					title={ __( 'Location', '4wp-weather' ) }
					initialOpen={ false }
				>
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
					title={ __( 'Visible fields', '4wp-weather' ) }
					initialOpen={ true }
				>
					<PanelRow>
						<ToggleControl
							label={ __( 'Location name', '4wp-weather' ) }
							checked={ !! showLocationName }
							onChange={ ( v ) =>
								setAttributes( { showLocationName: v } )
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Temperature', '4wp-weather' ) }
							checked={ !! showTemperature }
							onChange={ ( v ) =>
								setAttributes( { showTemperature: v } )
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Feels-like temperature', '4wp-weather' ) }
							checked={ !! showFeelsLike }
							onChange={ ( v ) =>
								setAttributes( { showFeelsLike: v } )
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Weather condition', '4wp-weather' ) }
							checked={ !! showCondition }
							onChange={ ( v ) =>
								setAttributes( { showCondition: v } )
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Humidity', '4wp-weather' ) }
							checked={ !! showHumidity }
							onChange={ ( v ) =>
								setAttributes( { showHumidity: v } )
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Pressure', '4wp-weather' ) }
							checked={ !! showPressure }
							onChange={ ( v ) =>
								setAttributes( { showPressure: v } )
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Wind speed', '4wp-weather' ) }
							checked={ !! showWindSpeed }
							onChange={ ( v ) =>
								setAttributes( { showWindSpeed: v } )
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Sunrise', '4wp-weather' ) }
							checked={ !! showSunrise }
							onChange={ ( v ) =>
								setAttributes( { showSunrise: v } )
							}
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Sunset', '4wp-weather' ) }
							checked={ !! showSunset }
							onChange={ ( v ) =>
								setAttributes( { showSunset: v } )
							}
						/>
					</PanelRow>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{/*
					Mirror frontend card markup (templates/card-openweathermap.php) so Styles
					and Visible fields match the published block.
				*/}
				<div className="forwp-weather__card">
					<p className="forwp-weather__status screen-reader-text">
						{ __( 'Editor preview — values load on the front end.', '4wp-weather' ) }
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
											'Enable at least one row under “Visible fields”.',
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
											className="forwp-weather__label"
										>
											{ row.label }
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
