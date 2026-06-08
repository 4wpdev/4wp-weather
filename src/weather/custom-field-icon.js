/**
 * Renders a custom SVG icon from media library or legacy inline markup.
 */
import { useAttachmentSvg } from './use-attachment-svg';

/**
 * @param {Object} props
 * @param {Object} props.presentation
 * @param {Object} props.style
 */
export function CustomFieldIcon( { presentation, style } ) {
	const attachmentId = presentation.customIconId || 0;
	const { svg: attachmentSvg } = useAttachmentSvg( attachmentId );
	const svg = attachmentSvg || presentation.customSvg || '';

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
