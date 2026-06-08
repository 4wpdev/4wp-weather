/**
 * Custom icon picker — WordPress media library (SVG only).
 */
import { __ } from '@wordpress/i18n';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button, BaseControl } from '@wordpress/components';
import { buildIconStyle, buildLabelInnerStyle } from './icon-style-utils';
import { useAttachmentSvg } from './use-attachment-svg';

function isSvgMedia( media ) {
	if ( ! media ) {
		return false;
	}

	if ( media.mime === 'image/svg+xml' ) {
		return true;
	}

	return typeof media.url === 'string' && media.url.toLowerCase().endsWith( '.svg' );
}

function CustomIconPreview( { svg, presentation } ) {
	if ( ! svg ) {
		return (
			<span className="forwp-weather-custom-icon__placeholder">
				{ __( 'No icon selected', '4wp-weather' ) }
			</span>
		);
	}

	const style = {
		...buildLabelInnerStyle( presentation ),
		...buildIconStyle( presentation ),
	};

	return (
		<span
			className="forwp-weather-custom-icon__preview-svg"
			style={ style }
			aria-hidden="true"
			dangerouslySetInnerHTML={ { __html: svg } }
		/>
	);
}

/**
 * @param {Object}   props
 * @param {Object}   props.presentation Resolved field row.
 * @param {Function} props.onChange
 */
export function CustomIconPicker( { presentation, onChange } ) {
	const attachmentId = presentation.customIconId || 0;
	const { svg, media, isResolving } = useAttachmentSvg( attachmentId );
	const previewSvg = svg || presentation.customSvg || '';

	return (
		<BaseControl
			label={ __( 'Custom icon', '4wp-weather' ) }
			className="forwp-weather-custom-icon"
		>
			<div className="forwp-weather-custom-icon__current">
				<CustomIconPreview
					svg={ previewSvg }
					presentation={ presentation }
				/>
				{ isResolving && (
					<span className="forwp-weather-custom-icon__loading">
						{ __( 'Loading…', '4wp-weather' ) }
					</span>
				) }
				{ media?.title && (
					<span className="forwp-weather-custom-icon__filename">
						{ media.title }
					</span>
				) }
			</div>
			<div className="forwp-weather-custom-icon__actions">
				<MediaUploadCheck>
					<MediaUpload
						onSelect={ ( selected ) => {
							if ( ! isSvgMedia( selected ) ) {
								return;
							}

							onChange( {
								customIconId: selected.id,
								customSvg: '',
							} );
						} }
						allowedTypes={ [ 'image' ] }
						value={ attachmentId || undefined }
						render={ ( { open } ) => (
							<Button variant="secondary" onClick={ open }>
								{ attachmentId
									? __( 'Replace SVG', '4wp-weather' )
									: __( 'Select SVG', '4wp-weather' ) }
							</Button>
						) }
					/>
				</MediaUploadCheck>
				{ attachmentId > 0 && (
					<Button
						variant="tertiary"
						isDestructive
						onClick={ () =>
							onChange( {
								customIconId: 0,
								customSvg: '',
							} )
						}
					>
						{ __( 'Remove', '4wp-weather' ) }
					</Button>
				) }
			</div>
			<p className="forwp-weather-custom-icon__help">
				{ __(
					'Choose an SVG file from the Media Library. Use currentColor in the SVG so icon color settings apply.',
					'4wp-weather'
				) }
			</p>
		</BaseControl>
	);
}
