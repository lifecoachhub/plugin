/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Offer types the shop can show, in display order. Must match render.php.
 */
export const TYPE_LABELS = {
	session_type: __( 'Sessions', 'lifecoachhub' ),
	coaching_package: __( 'Packages', 'lifecoachhub' ),
	coach_course: __( 'Courses', 'lifecoachhub' ),
	group_coaching_offer: __( 'Group coaching', 'lifecoachhub' ),
	coach_membership: __( 'Memberships', 'lifecoachhub' ),
	digital_product: __( 'Digital products', 'lifecoachhub' ),
	coach_offer: __( 'Bundles', 'lifecoachhub' ),
};

export const CADENCE_LABELS = {
	monthly: __( '/ month', 'lifecoachhub' ),
	yearly: __( '/ year', 'lifecoachhub' ),
};
