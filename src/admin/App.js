/**
 * Tabbed admin UI: Providers + Documentation.
 */
import { __, sprintf } from '@wordpress/i18n';
import { useState, useEffect, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import {
	Card,
	CardBody,
	CardHeader,
	Button,
	TextControl,
	Spinner,
	Notice,
	ExternalLink,
	ToggleControl,
} from '@wordpress/components';

const SETTINGS_PATH = '/forwp-weather/v1/settings';

const PREVIEW_PATH = '/forwp-weather/v1/preview';

const OWM_SLUG = 'openweathermap';

/** Safety cap for repeating mask chars if option value were abnormal. */
const API_KEY_MASK_MAX_LEN = 512;

function maskForSavedApiKey( length ) {
	const n =
		typeof length === 'number' && length > 0
			? Math.min( length, API_KEY_MASK_MAX_LEN )
			: 0;
	return n > 0 ? '*'.repeat( n ) : '';
}

function setupApiFetch() {
	if (
		typeof window === 'undefined' ||
		! window.forwpWeatherAdmin ||
		window.forwpWeatherAdmin.__middlewareApplied
	) {
		return;
	}
	const { restRoot, nonce } = window.forwpWeatherAdmin;
	const root =
		restRoot.endsWith( '/' ) ? restRoot : `${ restRoot }/`;
	apiFetch.use( apiFetch.createRootURLMiddleware( root ) );
	apiFetch.use( apiFetch.createNonceMiddleware( nonce ) );
	window.forwpWeatherAdmin.__middlewareApplied = true;
}

/**
 * Default hero when no provider card is selected.
 */
function ProvidersPlaceholderIntro() {
	return (
		<Card className="forwp-weather-intro-card">
			<CardBody>
				<h3 className="forwp-weather-intro-card__title">
					{ __( 'About this plugin', '4wp-weather' ) }
				</h3>
				<p className="forwp-weather-intro-card__text">
					{ __(
						'4WP Weather adds a block that shows current conditions on your site. Data is loaded through WordPress (AJAX), cached for performance, and API secrets stay on the server.',
						'4wp-weather'
					) }
				</p>
				<p className="forwp-weather-intro-card__text">
					{ __(
						'Implemented providers can receive an API key below the hood; roadmap entries appear as stubs until their integration ships.',
						'4wp-weather'
					) }
				</p>
				<p className="forwp-weather-intro-card__cta">
					{ __(
						'Pick a provider card in the registry below to configure credentials (if live), set preview coordinates, and load a live admin preview.',
						'4wp-weather'
					) }
				</p>
			</CardBody>
		</Card>
	);
}

/**
 * Planned-provider schematic OR live preview via REST (same stack as the block).
 */
function ProviderPreviewPanel( { row, refreshTick } ) {
	const [ panelLoading, setPanelLoading ] = useState( false );
	const [ payload, setPayload ] = useState( null );

	useEffect( () => {
		if ( ! row?.implemented ) {
			setPayload( null );
			return undefined;
		}

		let cancelled = false;
		setPanelLoading( true );

		const path = `${ PREVIEW_PATH }?provider=${ encodeURIComponent(
			row.slug
		) }`;

		apiFetch( { path } )
			.then( ( data ) => {
				if ( ! cancelled ) {
					setPayload( data );
				}
			} )
			.catch( ( e ) => {
				if ( ! cancelled ) {
					setPayload( {
						weather: null,
						query: null,
						error:
							e?.message ||
							__( 'Preview request failed.', '4wp-weather' ),
						code: 'fetch_error',
					} );
				}
			} )
			.finally( () => {
				if ( ! cancelled ) {
					setPanelLoading( false );
				}
			} );

		return () => {
			cancelled = true;
		};
	}, [ row?.implemented, row?.slug, refreshTick ] );

	if ( ! row ) {
		return null;
	}

	if ( ! row.implemented ) {
		return (
			<div className="forwp-weather-preview">
				<p className="forwp-weather-preview__caption">
					{ __( 'Frontend preview (schematic)', '4wp-weather' ) }
				</p>
				<div className="forwp-weather-preview__frame">
					<div className="forwp-weather-preview__chrome">
						<span className="forwp-weather-preview__dot" />
						<span className="forwp-weather-preview__dot" />
						<span className="forwp-weather-preview__dot" />
						<span className="forwp-weather-preview__chrome-label">
							{ row.label }
						</span>
					</div>
					<div className="forwp-weather-preview__card">
						<table className="forwp-weather-preview__table">
							<tbody>
								<tr>
									<th>{ __( 'Location', '4wp-weather' ) }</th>
									<td>—</td>
								</tr>
								<tr>
									<th>{ __( 'Temperature', '4wp-weather' ) }</th>
									<td>— °C</td>
								</tr>
								<tr>
									<th>{ __( 'Condition', '4wp-weather' ) }</th>
									<td>—</td>
								</tr>
							</tbody>
						</table>
						<div
							className="forwp-weather-preview__filler"
							aria-hidden="true"
						/>
					</div>
					<p className="forwp-weather-preview__stub-note">
						{ __(
							'When this provider is implemented, live values will replace the placeholders.',
							'4wp-weather'
						) }
					</p>
				</div>
			</div>
		);
	}

	const weather = payload?.weather;
	const query = payload?.query;
	const prevError = payload?.error;

	let locationLabel = '—';
	let tempLabel = '—';
	let condLabel = '—';

	if ( weather && typeof weather === 'object' ) {
		const loc = [];
		if ( weather.locationName ) {
			loc.push( weather.locationName );
		}
		if ( weather.country ) {
			loc.push( weather.country );
		}
		if ( loc.length ) {
			locationLabel = loc.join( ', ' );
		}

		if (
			typeof weather.temperature === 'number' &&
			! Number.isNaN( weather.temperature )
		) {
			tempLabel = `${
				Math.round( weather.temperature * 10 ) / 10
			} °C`;
		}
		if ( weather.condition ) {
			condLabel = weather.condition;
		}
	}

	const showFiller =
		! panelLoading &&
		! prevError &&
		( ! weather || typeof weather !== 'object' );

	return (
		<div className="forwp-weather-preview">
			<p className="forwp-weather-preview__caption">
				{ __( 'Frontend preview (live)', '4wp-weather' ) }
			</p>
			<div className="forwp-weather-preview__frame">
				<div className="forwp-weather-preview__chrome">
					<span className="forwp-weather-preview__dot" />
					<span className="forwp-weather-preview__dot" />
					<span className="forwp-weather-preview__dot" />
					<span className="forwp-weather-preview__chrome-label">
						{ row.label }
					</span>
				</div>
				<div className="forwp-weather-preview__card">
					{ panelLoading && (
						<div
							className="forwp-weather-preview__loading"
							aria-busy="true"
						>
							<Spinner />
						</div>
					) }
					{ prevError && ! panelLoading && (
						<div className="forwp-weather-preview__error">
							<Notice status="warning" isDismissible={ false }>
								{ prevError }
							</Notice>
						</div>
					) }
					<table className="forwp-weather-preview__table">
						<tbody>
							<tr>
								<th>{ __( 'Location', '4wp-weather' ) }</th>
								<td>{ locationLabel }</td>
							</tr>
							<tr>
								<th>{ __( 'Temperature', '4wp-weather' ) }</th>
								<td>{ tempLabel }</td>
							</tr>
							<tr>
								<th>{ __( 'Condition', '4wp-weather' ) }</th>
								<td>{ condLabel }</td>
							</tr>
						</tbody>
					</table>
					{ showFiller && (
						<div
							className="forwp-weather-preview__filler"
							aria-hidden="true"
						/>
					) }
				</div>
			</div>
			{ query && (
				<p className="forwp-weather-preview__meta">
					{ sprintf(
						/* translators: 1: latitude, 2: longitude, 3: provider slug */
						__(
							'Preview query: %1$f°, %2$f° · %3$s',
							'4wp-weather'
						),
						query.latitude,
						query.longitude,
						query.provider
					) }
				</p>
			) }
		</div>
	);
}

function ProvidersTab( { onOpenWpCliDocs = () => {} } ) {
	setupApiFetch();

	const [ loading, setLoading ] = useState( true );
	const [ saving, setSaving ] = useState( false );
	const [ error, setError ] = useState( null );
	const [ success, setSuccess ] = useState( false );
	const [ rows, setRows ] = useState( [] );
	const [ selectedSlug, setSelectedSlug ] = useState( null );
	const [ credentialProvider, setCredentialProvider ] = useState( OWM_SLUG );
	const [ apiKeyConfigured, setApiKeyConfigured ] = useState( false );
	const [ apiKeyLength, setApiKeyLength ] = useState( 0 );
	const [ apiKeyInput, setApiKeyInput ] = useState( '' );
	const [ apiKeyTouched, setApiKeyTouched ] = useState( false );
	const [ previewLatInput, setPreviewLatInput ] = useState( '' );
	const [ previewLonInput, setPreviewLonInput ] = useState( '' );
	const [ locationCooldownInput, setLocationCooldownInput ] =
		useState( '60' );
	const [ previewRefreshTick, setPreviewRefreshTick ] = useState( 0 );

	const load = useCallback( async ( opts = {} ) => {
		const refreshPreview = !! opts.refreshPreview;
		setLoading( true );
		setError( null );
		try {
			const data = await apiFetch( { path: SETTINGS_PATH } );
			setRows( data.providers || [] );
			setCredentialProvider( data.credential_provider || OWM_SLUG );
			setApiKeyConfigured( !! data.api_key_configured );
			setApiKeyLength(
				typeof data.api_key_length === 'number' &&
					data.api_key_length >= 0
					? data.api_key_length
					: 0
			);
			setApiKeyInput( '' );
			setApiKeyTouched( false );
			setPreviewLatInput(
				data.preview_latitude !== undefined &&
					data.preview_latitude !== null
					? String( data.preview_latitude )
					: ''
			);
			setPreviewLonInput(
				data.preview_longitude !== undefined &&
					data.preview_longitude !== null
					? String( data.preview_longitude )
					: ''
			);
			setLocationCooldownInput(
				data.location_change_cooldown_seconds !== undefined &&
					data.location_change_cooldown_seconds !== null
					? String( data.location_change_cooldown_seconds )
					: '60'
			);
			if ( refreshPreview ) {
				setPreviewRefreshTick( ( t ) => t + 1 );
			}
		} catch ( e ) {
			setError(
				e?.message ||
					__( 'Could not load settings.', '4wp-weather' )
			);
		} finally {
			setLoading( false );
		}
	}, [] );

	useEffect( () => {
		load();
	}, [ load ] );

	const selectedRow =
		selectedSlug && rows.length
			? rows.find( ( r ) => r.slug === selectedSlug )
			: null;

	const selectProvider = ( slug ) => {
		setSelectedSlug( slug );
		setSuccess( false );
		const row = rows.find( ( r ) => r.slug === slug );
		if ( row?.implemented ) {
			setCredentialProvider( slug );
		}
		setApiKeyInput( '' );
		setApiKeyTouched( false );
	};

	const save = async () => {
		if ( ! selectedRow?.implemented ) {
			return;
		}

		setSaving( true );
		setError( null );
		setSuccess( false );
		try {
			const payload = {
				credential_provider: credentialProvider,
				preview_latitude: previewLatInput.trim(),
				preview_longitude: previewLonInput.trim(),
				location_change_cooldown_seconds: locationCooldownInput.trim(),
			};
			if ( apiKeyTouched ) {
				payload.api_key = apiKeyInput;
			}
			const data = await apiFetch( {
				path: SETTINGS_PATH,
				method: 'POST',
				data: payload,
			} );
			setRows( data.providers || [] );
			setApiKeyConfigured( !! data.api_key_configured );
			setApiKeyLength(
				typeof data.api_key_length === 'number' &&
					data.api_key_length >= 0
					? data.api_key_length
					: 0
			);
			setApiKeyInput( '' );
			setApiKeyTouched( false );
			setPreviewLatInput(
				data.preview_latitude !== undefined &&
					data.preview_latitude !== null
					? String( data.preview_latitude )
					: ''
			);
			setPreviewLonInput(
				data.preview_longitude !== undefined &&
					data.preview_longitude !== null
					? String( data.preview_longitude )
					: ''
			);
			setLocationCooldownInput(
				data.location_change_cooldown_seconds !== undefined &&
					data.location_change_cooldown_seconds !== null
					? String( data.location_change_cooldown_seconds )
					: '60'
			);
			setPreviewRefreshTick( ( t ) => t + 1 );
			setSuccess( true );
		} catch ( e ) {
			setError(
				e?.message ||
					__( 'Could not save settings.', '4wp-weather' )
			);
		} finally {
			setSaving( false );
		}
	};

	const onCardKeyDown = ( event, slug ) => {
		if ( event.key === 'Enter' || event.key === ' ' ) {
			event.preventDefault();
			selectProvider( slug );
		}
	};

	if ( loading ) {
		return (
			<div className="forwp-weather-admin-loading">
				<Spinner />
			</div>
		);
	}

	const showSavedKeyMask =
		apiKeyConfigured && ! apiKeyTouched && apiKeyLength > 0;

	return (
		<div className="forwp-weather-admin-providers">
			{ error && (
				<Notice status="error" isDismissible onRemove={ () => setError( null ) }>
					{ error }
				</Notice>
			) }
			{ success && (
				<Notice
					status="success"
					isDismissible
					onRemove={ () => setSuccess( false ) }
				>
					{ __( 'Settings saved.', '4wp-weather' ) }
				</Notice>
			) }

			{ selectedSlug === null ? (
				<div className="forwp-weather-admin-detail-region">
					<ProvidersPlaceholderIntro />
				</div>
			) : (
				<div className="forwp-weather-admin-detail-region">
					<div className="forwp-weather-provider-detail-head">
						<h3 className="forwp-weather-admin-section-title forwp-weather-provider-detail-head__title">
							{ selectedRow?.label ||
								__( 'Provider', '4wp-weather' ) }
						</h3>
						<Button
							variant="tertiary"
							onClick={ () => setSelectedSlug( null ) }
						>
							{ __( '← Back to overview', '4wp-weather' ) }
						</Button>
					</div>

					<div className="forwp-weather-split">
						<div className="forwp-weather-split__col forwp-weather-split__settings">
							{ selectedRow?.implemented ? (
								<>
									<p className="forwp-weather-admin-muted forwp-weather-split__lead">
										{ __(
											'API secret is stored in WordPress only; blocks request weather through AJAX without exposing keys.',
											'4wp-weather'
										) }
									</p>
									<div className="forwp-weather-admin-panel forwp-weather-admin-panel--embedded">
										<TextControl
											label={ __( 'API key', '4wp-weather' ) }
											type="password"
											autoComplete="off"
											readOnly={ showSavedKeyMask }
											value={
												showSavedKeyMask
													? maskForSavedApiKey(
															apiKeyLength
													  )
													: apiKeyInput
											}
											onFocus={ () => {
												if ( showSavedKeyMask ) {
													setApiKeyTouched( true );
													setApiKeyInput( '' );
												}
											} }
											onBlur={ () => {
												if (
													apiKeyConfigured &&
													apiKeyTouched &&
													apiKeyInput === ''
												) {
													setApiKeyTouched( false );
												}
											} }
											onChange={ ( v ) => {
												setApiKeyInput( v );
												setApiKeyTouched( true );
											} }
											help={
												apiKeyConfigured &&
												! apiKeyTouched
													? __(
															'A key is saved. Focus the field to replace it, or leave blank on save to clear.',
															'4wp-weather'
													  )
													: __(
															'Stored server-side only; never exposed to the frontend.',
															'4wp-weather'
													  )
											}
										/>
										{ selectedRow?.api_key_docs_url &&
											selectedRow?.api_key_docs_link_label && (
												<p className="forwp-weather-api-key-help">
													{ selectedRow.api_key_help_intro ? (
														<>
															{
																selectedRow.api_key_help_intro
															}{ ' ' }
														</>
													) : null }
													<ExternalLink
														href={ selectedRow.api_key_docs_url }
													>
														{
															selectedRow.api_key_docs_link_label
														}
													</ExternalLink>
												</p>
											) }
										<div className="forwp-weather-settings-row">
											<TextControl
												label={ __(
													'Lat (°)',
													'4wp-weather'
												) }
												type="text"
												inputMode="decimal"
												autoComplete="off"
												value={ previewLatInput }
												onChange={ setPreviewLatInput }
												help={ __(
													'−90…90. Empty = default.',
													'4wp-weather'
												) }
											/>
											<TextControl
												label={ __(
													'Lon (°)',
													'4wp-weather'
												) }
												type="text"
												inputMode="decimal"
												autoComplete="off"
												value={ previewLonInput }
												onChange={ setPreviewLonInput }
												help={ __(
													'−180…180. Set with lat.',
													'4wp-weather'
												) }
											/>
											<TextControl
												label={ __(
													'Location cooldown (s)',
													'4wp-weather'
												) }
												type="number"
												min={ 0 }
												max={ 86400 }
												value={ locationCooldownInput }
												onChange={ setLocationCooldownInput }
												help={ __(
													'Min sec btw search/geo. 0=off.',
													'4wp-weather'
												) }
											/>
										</div>
										<div
											className="forwp-weather-toolbar"
											role="group"
											aria-label={ __(
												'Credential actions',
												'4wp-weather'
											) }
										>
											<div className="forwp-weather-toolbar__primary">
												<Button
													variant="primary"
													className="forwp-weather-btn-dashicon"
													onClick={ save }
													disabled={ saving }
													isBusy={ saving }
												>
													<span
														className="dashicons dashicons-saved"
														aria-hidden="true"
													/>
													<span>{ __( 'Save', '4wp-weather' ) }</span>
												</Button>
												<Button
													variant="secondary"
													className="forwp-weather-btn-dashicon"
													onClick={ () =>
														load( {
															refreshPreview: true,
														} )
													}
													disabled={ saving }
												>
													<span
														className="dashicons dashicons-update"
														aria-hidden="true"
													/>
													<span>{ __( 'Reload', '4wp-weather' ) }</span>
												</Button>
											</div>
											<div className="forwp-weather-toolbar__secondary">
												<Button
													variant="tertiary"
													className="forwp-weather-btn-dashicon"
													onClick={ onOpenWpCliDocs }
												>
													<span
														className="dashicons dashicons-editor-code"
														aria-hidden="true"
													/>
													<span>{ __( 'WP-CLI', '4wp-weather' ) }</span>
												</Button>
												<p className="forwp-weather-toolbar__hint">
													{ __(
														'Opens the Documentation tab (WP-CLI cache flush).',
														'4wp-weather'
													) }
												</p>
											</div>
										</div>
									</div>
								</>
							) : (
								<Notice status="info" isDismissible={ false }>
									{ __(
										'This provider is listed for architecture and roadmap planning only. No credentials or live requests are available yet.',
										'4wp-weather'
									) }
								</Notice>
							) }
						</div>
						<div className="forwp-weather-split__col forwp-weather-split__preview">
							<ProviderPreviewPanel
								row={ selectedRow }
								refreshTick={ previewRefreshTick }
							/>
						</div>
					</div>
				</div>
			) }

			<h3 className="forwp-weather-admin-section-title forwp-weather-registry-title">
				{ __( 'Provider registry', '4wp-weather' ) }
			</h3>
			<p className="forwp-weather-admin-muted forwp-weather-registry-hint">
				{ __( 'Click a card to configure or preview.', '4wp-weather' ) }
			</p>

			<div className="forwp-weather-provider-grid">
				{ rows.map( ( row ) => (
					<div
						key={ row.slug }
						className={
							'forwp-weather-provider-card-wrap' +
							( selectedSlug === row.slug ? ' is-selected' : '' )
						}
						role="button"
						tabIndex={ 0 }
						aria-pressed={ selectedSlug === row.slug }
						aria-label={ sprintf(
							/* translators: %s: provider name */
							__( 'Open details for %s', '4wp-weather' ),
							row.label
						) }
						onClick={ () => selectProvider( row.slug ) }
						onKeyDown={ ( e ) => onCardKeyDown( e, row.slug ) }
					>
						<Card className="forwp-weather-provider-card">
							<CardHeader>
								<div className="forwp-weather-provider-card-head">
									<div>
										<div className="forwp-weather-provider-label">
											{ row.label }
										</div>
										<div className="forwp-weather-provider-slug">
											<code>{ row.slug }</code>
										</div>
									</div>
									<span
										className={
											row.implemented
												? 'forwp-weather-badge forwp-weather-badge--live'
												: 'forwp-weather-badge forwp-weather-badge--planned'
										}
									>
										{ row.implemented
											? __( 'Live', '4wp-weather' )
											: __( 'Planned', '4wp-weather' ) }
									</span>
								</div>
							</CardHeader>
							<CardBody className="forwp-weather-provider-card-footer">
								<p className="forwp-weather-provider-status">
									{ row.status }
								</p>
							</CardBody>
						</Card>
					</div>
				) ) }
			</div>
		</div>
	);
}

function DocumentationTab() {
	setupApiFetch();

	const [ loading, setLoading ] = useState( true );
	const [ saving, setSaving ] = useState( false );
	const [ showBar, setShowBar ] = useState( false );
	const [ outputJsonLd, setOutputJsonLd ] = useState( false );
	const [ error, setError ] = useState( null );

	useEffect( () => {
		let cancelled = false;
		( async () => {
			setLoading( true );
			setError( null );
			try {
				const data = await apiFetch( { path: SETTINGS_PATH } );
				if ( ! cancelled ) {
					setShowBar( !! data.show_admin_bar_weather );
					setOutputJsonLd( !! data.output_json_ld );
				}
			} catch ( e ) {
				if ( ! cancelled ) {
					setError(
						e?.message ||
							__( 'Could not load settings.', '4wp-weather' )
					);
				}
			} finally {
				if ( ! cancelled ) {
					setLoading( false );
				}
			}
		} )();
		return () => {
			cancelled = true;
		};
	}, [] );

	const onToggleBar = async ( next ) => {
		setSaving( true );
		setError( null );
		try {
			await apiFetch( {
				path: SETTINGS_PATH,
				method: 'POST',
				data: { show_admin_bar_weather: next },
			} );
			setShowBar( next );
		} catch ( e ) {
			setError(
				e?.message || __( 'Could not save preference.', '4wp-weather' )
			);
		} finally {
			setSaving( false );
		}
	};

	const onToggleJsonLd = async ( next ) => {
		setSaving( true );
		setError( null );
		try {
			await apiFetch( {
				path: SETTINGS_PATH,
				method: 'POST',
				data: { output_json_ld: next },
			} );
			setOutputJsonLd( next );
		} catch ( e ) {
			setError(
				e?.message || __( 'Could not save preference.', '4wp-weather' )
			);
		} finally {
			setSaving( false );
		}
	};

	return (
		<div className="forwp-weather-admin-docs forwp-weather-docs-layout">
			<div className="forwp-weather-docs-layout__main">
				<Card>
					<CardBody>
						<h3>{ __( 'Overview', '4wp-weather' ) }</h3>
						<p>
							{ __(
								'4WP Weather exposes a block that loads current conditions via AJAX. Requests are cached server-side; your API secret never reaches browsers.',
								'4wp-weather'
							) }
						</p>
						<p>
							{ __(
								'OpenWeatherMap is the first implemented upstream; other providers appear as roadmap stubs until integrations ship.',
								'4wp-weather'
							) }
						</p>
						<p>
							{ __(
								'The Providers tab can store optional preview latitude and longitude. If either is empty, the plugin uses a default map point (developers may replace it with the forwp_weather_default_preview_coordinates filter).',
								'4wp-weather'
							) }
						</p>
						<p>
							{ __(
								'Under Providers, when you configure a live provider, you can set how many seconds must pass before a visitor can change location again (search or geolocation button).',
								'4wp-weather'
							) }
						</p>

						<h3
							id="forwp-weather-docs-wp-cli"
							className="forwp-weather-docs-cli-title"
							tabIndex={ -1 }
						>
							{ __( 'WP-CLI', '4wp-weather' ) }
						</h3>
						<p>
							{ __(
								'Flush cached responses so the next AJAX call hits the upstream API:',
								'4wp-weather'
							) }
						</p>
						<pre className="forwp-weather-cli-snippet">
							<code>wp forwp-weather flush-cache</code>
						</pre>
					</CardBody>
				</Card>
			</div>
			<div className="forwp-weather-docs-layout__aside">
				<Card>
					<CardBody>
						<h3>{ __( 'Admin toolbar', '4wp-weather' ) }</h3>
						{ error && (
							<Notice
								status="error"
								isDismissible
								onRemove={ () => setError( null ) }
							>
								{ error }
							</Notice>
						) }
						{ loading ? (
							<div className="forwp-weather-docs-aside-loading">
								<Spinner />
							</div>
						) : (
							<div className="forwp-weather-docs-aside-toggles">
								<ToggleControl
									label={ __(
										'Show weather in the admin bar',
										'4wp-weather'
									) }
									help={ __(
										'Uses preview latitude/longitude and the credential provider from the Providers tab (same as the live preview). Only visible to site administrators.',
										'4wp-weather'
									) }
									checked={ showBar }
									disabled={ saving }
									onChange={ onToggleBar }
								/>
								<ToggleControl
									label={ __(
										'Structured data (JSON-LD)',
										'4wp-weather'
									) }
									help={ __(
										'When on, each weather block adds a small Observation script after data loads. Public pages only — not in wp-admin. No rich-result guarantee in Google.',
										'4wp-weather'
									) }
									checked={ outputJsonLd }
									disabled={ saving }
									onChange={ onToggleJsonLd }
								/>
							</div>
						) }
					</CardBody>
				</Card>
			</div>
		</div>
	);
}

export default function App() {
	const [ activeTab, setActiveTab ] = useState( 'providers' );
	const [ scrollWpCliIntoView, setScrollWpCliIntoView ] =
		useState( false );

	const openWpCliDocumentation = useCallback( () => {
		setActiveTab( 'documentation' );
		setScrollWpCliIntoView( true );
	}, [] );

	useEffect( () => {
		if ( activeTab !== 'documentation' || ! scrollWpCliIntoView ) {
			return undefined;
		}
		const timerId = window.setTimeout( () => {
			const heading = document.getElementById(
				'forwp-weather-docs-wp-cli'
			);
			heading?.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			heading?.focus( { preventScroll: true } );
			setScrollWpCliIntoView( false );
		}, 50 );
		return () => window.clearTimeout( timerId );
	}, [ activeTab, scrollWpCliIntoView ] );

	return (
		<div className="forwp-weather-admin-app">
			<div className="forwp-weather-tab-panel components-tab-panel">
				<div
					className="components-tab-panel__tabs"
					role="tablist"
					aria-label={ __( '4WP Weather', '4wp-weather' ) }
				>
					<button
						type="button"
						role="tab"
						id="forwp-weather-tab-providers"
						className={
							'components-button components-tab-panel__tabs-item forwp-weather-tab-providers' +
							( activeTab === 'providers' ? ' is-active' : '' )
						}
						aria-selected={ activeTab === 'providers' }
						aria-controls="forwp-weather-panel-providers"
						tabIndex={ activeTab === 'providers' ? 0 : -1 }
						onClick={ () => setActiveTab( 'providers' ) }
					>
						{ __( 'Providers', '4wp-weather' ) }
					</button>
					<button
						type="button"
						role="tab"
						id="forwp-weather-tab-documentation"
						className={
							'components-button components-tab-panel__tabs-item forwp-weather-tab-docs' +
							( activeTab === 'documentation' ? ' is-active' : '' )
						}
						aria-selected={ activeTab === 'documentation' }
						aria-controls="forwp-weather-panel-documentation"
						tabIndex={ activeTab === 'documentation' ? 0 : -1 }
						onClick={ () => setActiveTab( 'documentation' ) }
					>
						{ __( 'Documentation', '4wp-weather' ) }
					</button>
				</div>
				<div
					id="forwp-weather-panel-providers"
					role="tabpanel"
					aria-labelledby="forwp-weather-tab-providers"
					className="components-tab-panel__tab-content"
					hidden={ activeTab !== 'providers' }
				>
					<ProvidersTab onOpenWpCliDocs={ openWpCliDocumentation } />
				</div>
				<div
					id="forwp-weather-panel-documentation"
					role="tabpanel"
					aria-labelledby="forwp-weather-tab-documentation"
					className="components-tab-panel__tab-content"
					hidden={ activeTab !== 'documentation' }
				>
					<DocumentationTab />
				</div>
			</div>
		</div>
	);
}
