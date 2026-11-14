/**
 * Image shapes offered in the editor.
 */
export const ASPECT_RATIOS = [ '16/9', '4/3', '3/2', '1', '3/4', 'auto' ];

/**
 * CSS custom properties for the block's style attributes. Empty values fall back to the stylesheet.
 *
 * @param {Object} attributes Block attributes.
 * @return {Object} Style object of CSS custom properties.
 */
export function styleVars( attributes ) {
	const vars = {
		'--lifecoachhub-shop-columns': attributes.columns,
		'--lifecoachhub-shop-card-bg': attributes.cardBackground,
		'--lifecoachhub-shop-card-border-color': attributes.cardBorderColor,
		'--lifecoachhub-shop-card-border-width': attributes.cardBorderWidth,
		'--lifecoachhub-shop-radius': attributes.cardRadius,
		'--lifecoachhub-shop-card-padding': attributes.cardPadding,
		'--lifecoachhub-shop-button-bg': attributes.buttonBackground,
		'--lifecoachhub-shop-button-text': attributes.buttonTextColor,
		'--lifecoachhub-shop-button-radius': attributes.buttonRadius,
		'--lifecoachhub-shop-filter-active-bg':
			attributes.filterActiveBackground,
		'--lifecoachhub-shop-filter-active-text':
			attributes.filterActiveTextColor,
		'--lifecoachhub-shop-title-size': attributes.titleFontSize,
		'--lifecoachhub-shop-price-size': attributes.priceFontSize,
		'--lifecoachhub-shop-image-ratio':
			attributes.imageAspectRatio === 'auto'
				? ''
				: attributes.imageAspectRatio,
		'--lifecoachhub-shop-image-fit': attributes.imageFit,
	};

	return Object.fromEntries(
		Object.entries( vars ).filter(
			( [ , value ] ) =>
				value !== '' && value !== undefined && value !== null
		)
	);
}

/**
 * Offers to show, in display order, for the current settings.
 *
 * @param {Array}  allOffers  Offers from the store.
 * @param {Object} attributes Block attributes.
 * @param {Object} typeLabels Known offer types.
 * @return {Array} Offers to show.
 */
export function chooseOffers( allOffers, attributes, typeLabels ) {
	const known = allOffers.filter( ( offer ) => typeLabels[ offer.type ] );
	let offers;

	if ( attributes.selectionMode === 'manual' ) {
		offers = attributes.selectedOffers
			.map( ( id ) => known.find( ( offer ) => offer.id === id ) )
			.filter( Boolean );
	} else {
		offers = known.filter(
			( offer ) =>
				attributes.offerTypes.length === 0 ||
				attributes.offerTypes.includes( offer.type )
		);
	}

	return attributes.limit > 0 ? offers.slice( 0, attributes.limit ) : offers;
}
