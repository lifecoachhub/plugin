/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';
import { createBlock, getBlockType } from '@wordpress/blocks';
import { BlockPreview } from '@wordpress/block-editor';
import { Button, Modal } from '@wordpress/components';

/**
 * Settings that belong to the coach's content, not the look, so a layout never changes them.
 */
const KEEP = [ 'selectionMode', 'selectedOffers', 'filterLabels' ];

/**
 * Text the coach may have typed; kept when set, otherwise the layout's suggestion is used.
 */
const KEEP_IF_SET = [ 'buyLabel', 'detailsLabel' ];

/**
 * Layouts shared by PHP (the same presets the block patterns use).
 *
 * @return {Array} Layouts: { name, title, description, attributes }.
 */
export function getLayouts() {
	return Array.isArray( window.lifecoachhubShopLayouts )
		? window.lifecoachhubShopLayouts
		: [];
}

/**
 * Block attributes after applying a layout: every look setting goes back to its default,
 * then the layout's settings apply. Content and alignment stay as the coach set them.
 *
 * @param {string} blockName Block name.
 * @param {Object} current   Current attributes.
 * @param {Object} preset    Layout attributes.
 * @return {Object} New attributes.
 */
export function applyLayout( blockName, current, preset ) {
	const definitions = getBlockType( blockName )?.attributes ?? {};
	const next = {};

	Object.entries( definitions ).forEach( ( [ key, definition ] ) => {
		if ( KEEP.includes( key ) || key === 'align' || key === 'className' ) {
			return;
		}
		if ( KEEP_IF_SET.includes( key ) && current[ key ] ) {
			return;
		}
		if ( 'default' in definition ) {
			next[ key ] = definition.default;
		}
	} );

	Object.entries( preset ).forEach( ( [ key, value ] ) => {
		if (
			key === 'align' ||
			key === 'className' ||
			KEEP.includes( key ) ||
			( KEEP_IF_SET.includes( key ) && current[ key ] )
		) {
			return;
		}
		next[ key ] = value;
	} );

	// Swap only the block style class, keeping any custom classes.
	const otherClasses = ( current.className ?? '' )
		.split( /\s+/ )
		.filter( ( name ) => name && ! name.startsWith( 'is-style-' ) );
	const styleClass = ( preset.className ?? '' )
		.split( /\s+/ )
		.find( ( name ) => name.startsWith( 'is-style-' ) );
	next.className =
		[ ...otherClasses, styleClass ].filter( Boolean ).join( ' ' ) ||
		undefined;

	return next;
}

export default function LayoutPicker( {
	blockName,
	attributes,
	setAttributes,
	onClose,
} ) {
	const layouts = getLayouts();

	// Preview each layout with the coach's own offers, capped so the picker stays quick.
	const previews = useMemo(
		() =>
			layouts.map( ( layout ) => {
				const next = {
					...attributes,
					...applyLayout( blockName, attributes, layout.attributes ),
				};
				next.limit = next.limit > 0 ? Math.min( next.limit, 3 ) : 3;

				return {
					layout,
					blocks: [ createBlock( blockName, next ) ],
				};
			} ),
		// Previews only need rebuilding when the modal opens.
		// eslint-disable-next-line react-hooks/exhaustive-deps
		[]
	);

	return (
		<Modal
			title={ __( 'Choose a layout', 'lifecoachhub' ) }
			onRequestClose={ onClose }
			size="large"
			className="lifecoachhub-shop-layouts"
		>
			<p className="lifecoachhub-shop-layouts__intro">
				{ __(
					'Pick a look for this shop. Your chosen offers and tab names stay the same, and you can fine-tune everything afterwards.',
					'lifecoachhub'
				) }
			</p>
			<div className="lifecoachhub-shop-layouts__grid">
				{ previews.map( ( { layout, blocks } ) => (
					<Button
						key={ layout.name }
						className="lifecoachhub-shop-layouts__item"
						onClick={ () => {
							setAttributes(
								applyLayout(
									blockName,
									attributes,
									layout.attributes
								)
							);
							onClose();
						} }
					>
						<span className="lifecoachhub-shop-layouts__preview">
							<BlockPreview
								blocks={ blocks }
								viewportWidth={ 1100 }
							/>
						</span>
						<span className="lifecoachhub-shop-layouts__title">
							{ layout.title }
						</span>
						<span className="lifecoachhub-shop-layouts__description">
							{ layout.description }
						</span>
					</Button>
				) ) }
			</div>
		</Modal>
	);
}
