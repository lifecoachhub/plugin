/**
 * WordPress dependencies
 */
import { store, getContext } from '@wordpress/interactivity';

/**
 * Whether an offer matches the active filter tab and search text.
 *
 * @param {Object} context Block context.
 * @param {Object} offer   Offer summary from the block context.
 * @return {boolean} True when the offer should be shown.
 */
function matches( context, offer ) {
	const typeMatches =
		context.activeType === 'all' || context.activeType === offer.type;
	const query = context.search.trim().toLowerCase();

	return typeMatches && ( query === '' || offer.haystack.includes( query ) );
}

/**
 * Offers in the order picked in the sort menu.
 *
 * @param {Object} context Block context.
 * @return {Array} Sorted offer summaries.
 */
function sortedOffers( context ) {
	const offers = [ ...context.offers ];

	if ( context.sort === 'price-asc' ) {
		offers.sort( ( a, b ) => a.price - b.price || a.index - b.index );
	} else if ( context.sort === 'price-desc' ) {
		offers.sort( ( a, b ) => b.price - a.price || a.index - b.index );
	} else if ( context.sort === 'name' ) {
		offers.sort(
			( a, b ) =>
				a.title.localeCompare( b.title, undefined, {
					sensitivity: 'base',
				} ) || a.index - b.index
		);
	} else {
		// "Featured": the order the coach hand-picked.
		offers.sort( ( a, b ) => a.index - b.index );
	}

	return offers;
}

store( 'lifecoachhub/shop', {
	state: {
		get isTabActive() {
			const context = getContext();
			return context.activeType === context.tab;
		},
		get isCardHidden() {
			const context = getContext();
			const offer = context.offers.find(
				( item ) => item.id === context.offerId
			);
			return ! offer || ! matches( context, offer );
		},
		get cardOrder() {
			const context = getContext();
			return String(
				sortedOffers( context ).findIndex(
					( item ) => item.id === context.offerId
				)
			);
		},
		get hasNoResults() {
			const context = getContext();
			return ! context.offers.some( ( offer ) =>
				matches( context, offer )
			);
		},
	},
	actions: {
		setType() {
			const context = getContext();
			context.activeType = context.tab;
		},
		setSearch( event ) {
			getContext().search = event.target.value;
		},
		setSort( event ) {
			getContext().sort = event.target.value;
		},
	},
} );
