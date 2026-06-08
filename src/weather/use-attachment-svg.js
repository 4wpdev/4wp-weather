/**
 * Load inline SVG markup from a media library attachment.
 */
import { useEffect, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * @param {number} attachmentId Media attachment ID.
 * @return {{svg: string, media: Object|null, isResolving: boolean}}
 */
export function useAttachmentSvg( attachmentId ) {
	const media = useSelect(
		( select ) => {
			if ( ! attachmentId ) {
				return null;
			}

			return select( 'core' ).getMedia( attachmentId );
		},
		[ attachmentId ]
	);

	const { getMedia } = useDispatch( 'core' );
	const [ svg, setSvg ] = useState( '' );
	const [ isResolving, setIsResolving ] = useState( false );

	useEffect( () => {
		if ( ! attachmentId ) {
			return;
		}

		getMedia( attachmentId );
	}, [ attachmentId ] );

	useEffect( () => {
		if ( ! media?.source_url ) {
			setSvg( '' );
			return;
		}

		let cancelled = false;
		setIsResolving( true );

		window
			.fetch( media.source_url, { credentials: 'same-origin' } )
			.then( ( response ) => {
				if ( ! response.ok ) {
					throw new Error( 'SVG fetch failed' );
				}

				return response.text();
			} )
			.then( ( markup ) => {
				if ( ! cancelled ) {
					setSvg( markup );
				}
			} )
			.catch( () => {
				if ( ! cancelled ) {
					setSvg( '' );
				}
			} )
			.finally( () => {
				if ( ! cancelled ) {
					setIsResolving( false );
				}
			} );

		return () => {
			cancelled = true;
		};
	}, [ media?.source_url ] );

	return { svg, media, isResolving };
}
