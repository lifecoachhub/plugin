<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file is included inside the block render callback, so its variables are local.
/**
 * Server render for the Life Coach Hub Shop block.
 *
 * @package LifeCoachHub
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner content (unused).
 * @var WP_Block $block      Block instance.
 */

use LifeCoachHub\Api\LifeCoachHub_Store;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$store = LifeCoachHub_Store::get();

if ( is_wp_error( $store ) ) {
	// Visitors see nothing; people who can edit the page see why.
	if ( current_user_can( 'edit_posts' ) ) {
		printf(
			'<div %1$s><p class="lifecoachhub-shop__notice">%2$s</p></div>',
			get_block_wrapper_attributes(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core.
			esc_html( $store->get_error_message() )
		);
	}
	return;
}

$type_labels = array(
	'session_type'         => __( 'Sessions', 'lifecoachhub' ),
	'coaching_package'     => __( 'Packages', 'lifecoachhub' ),
	'coach_course'         => __( 'Courses', 'lifecoachhub' ),
	'group_coaching_offer' => __( 'Group coaching', 'lifecoachhub' ),
	'coach_membership'     => __( 'Memberships', 'lifecoachhub' ),
	'digital_product'      => __( 'Digital products', 'lifecoachhub' ),
	'coach_offer'          => __( 'Bundles', 'lifecoachhub' ),
);

// Coach-renamed filter tabs (and card type labels).
$custom_labels = is_array( $attributes['filterLabels'] ?? null ) ? $attributes['filterLabels'] : array();
foreach ( $type_labels as $type => $label ) {
	if ( isset( $custom_labels[ $type ] ) && '' !== trim( (string) $custom_labels[ $type ] ) ) {
		$type_labels[ $type ] = sanitize_text_field( $custom_labels[ $type ] );
	}
}
$all_label = isset( $custom_labels['all'] ) && '' !== trim( (string) $custom_labels['all'] )
	? sanitize_text_field( $custom_labels['all'] )
	: __( 'All', 'lifecoachhub' );

$limit         = max( 0, (int) ( $attributes['limit'] ?? 0 ) );
$columns       = min( 6, max( 1, (int) ( $attributes['columns'] ?? 3 ) ) );
$new_tab       = ! empty( $attributes['openInNewTab'] );
$buy_label     = '' !== trim( (string) ( $attributes['buyLabel'] ?? '' ) ) ? $attributes['buyLabel'] : __( 'Buy now', 'lifecoachhub' );
$details_label = '' !== trim( (string) ( $attributes['detailsLabel'] ?? '' ) ) ? $attributes['detailsLabel'] : __( 'Learn more', 'lifecoachhub' );
$heading_level = min( 6, max( 2, (int) ( $attributes['headingLevel'] ?? 3 ) ) );
$heading_tag   = 'h' . $heading_level;
$manual        = 'manual' === ( $attributes['selectionMode'] ?? 'auto' );
$default_sort  = in_array( $attributes['defaultSort'] ?? 'name', array( 'name', 'price-asc', 'price-desc', 'manual' ), true ) ? $attributes['defaultSort'] : 'name';

$known_offers = array_values(
	array_filter(
		$store['offers'],
		function ( $offer ) use ( $type_labels ) {
			return isset( $offer['id'], $offer['type'], $offer['checkout_url'] ) && isset( $type_labels[ $offer['type'] ] );
		}
	)
);

if ( $manual ) {
	// Hand-picked offers, in the order the coach set. Offers removed in Life Coach Hub drop out.
	$by_id = array();
	foreach ( $known_offers as $offer ) {
		$by_id[ (string) $offer['id'] ] = $offer;
	}
	$offers = array();
	foreach ( (array) ( $attributes['selectedOffers'] ?? array() ) as $offer_id ) {
		if ( isset( $by_id[ (string) $offer_id ] ) ) {
			$offers[] = $by_id[ (string) $offer_id ];
		}
	}
} else {
	$selected_types = array_values( array_intersect( (array) ( $attributes['offerTypes'] ?? array() ), array_keys( $type_labels ) ) );
	$offers         = array_values(
		array_filter(
			$known_offers,
			function ( $offer ) use ( $selected_types ) {
				return empty( $selected_types ) || in_array( $offer['type'], $selected_types, true );
			}
		)
	);
}

if ( $limit > 0 ) {
	$offers = array_slice( $offers, 0, $limit );
}

if ( empty( $offers ) ) {
	if ( current_user_can( 'edit_posts' ) ) {
		printf(
			'<div %1$s><p class="lifecoachhub-shop__notice">%2$s</p></div>',
			get_block_wrapper_attributes(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core.
			esc_html__( 'No published offers to show yet. Offers shown in your Life Coach Hub public store appear here.', 'lifecoachhub' )
		);
	}
	return;
}

/**
 * Keep a style value only if it is a plain color, length or CSS variable, so attributes can't inject CSS.
 *
 * @param mixed  $value Raw attribute value.
 * @param string $kind  'color', 'length' or 'size'.
 * @return string
 */
$css_value = static function ( $value, $kind ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}

	$patterns = array(
		'color'  => '/^(#[0-9a-f]{3,8}|rgba?\([0-9.,%\s\/]+\)|hsla?\([0-9.,%\s\/deg]+\)|var\(--[a-z0-9-]+\)|transparent|currentcolor)$/i',
		'length' => '/^(0|\d*\.?\d+(px|em|rem|%|vw|vh))$/i',
		'size'   => '/^(\d*\.?\d+(px|em|rem|%|vw|vh)|var\(--[a-z0-9-]+\)|clamp\([0-9a-z.,%+\-*\/\s()]+\)|calc\([0-9a-z.,%+\-*\/\s()]+\)|min\([0-9a-z.,%+\-*\/\s()]+\)|max\([0-9a-z.,%+\-*\/\s()]+\))$/i',
	);

	return preg_match( $patterns[ $kind ], $value ) ? $value : '';
};

$aspect_ratios = array( '16/9', '4/3', '3/2', '1', '3/4' );

$style_vars = array(
	'--lifecoachhub-shop-columns'            => (string) $columns,
	'--lifecoachhub-shop-card-bg'            => $css_value( $attributes['cardBackground'] ?? '', 'color' ),
	'--lifecoachhub-shop-card-border-color'  => $css_value( $attributes['cardBorderColor'] ?? '', 'color' ),
	'--lifecoachhub-shop-card-border-width'  => $css_value( $attributes['cardBorderWidth'] ?? '', 'length' ),
	'--lifecoachhub-shop-radius'             => $css_value( $attributes['cardRadius'] ?? '', 'length' ),
	'--lifecoachhub-shop-card-padding'       => $css_value( $attributes['cardPadding'] ?? '', 'length' ),
	'--lifecoachhub-shop-button-bg'          => $css_value( $attributes['buttonBackground'] ?? '', 'color' ),
	'--lifecoachhub-shop-button-text'        => $css_value( $attributes['buttonTextColor'] ?? '', 'color' ),
	'--lifecoachhub-shop-button-radius'      => $css_value( $attributes['buttonRadius'] ?? '', 'length' ),
	'--lifecoachhub-shop-filter-active-bg'   => $css_value( $attributes['filterActiveBackground'] ?? '', 'color' ),
	'--lifecoachhub-shop-filter-active-text' => $css_value( $attributes['filterActiveTextColor'] ?? '', 'color' ),
	'--lifecoachhub-shop-title-size'         => $css_value( $attributes['titleFontSize'] ?? '', 'size' ),
	'--lifecoachhub-shop-price-size'         => $css_value( $attributes['priceFontSize'] ?? '', 'size' ),
	'--lifecoachhub-shop-image-ratio'        => in_array( $attributes['imageAspectRatio'] ?? '16/9', $aspect_ratios, true ) ? $attributes['imageAspectRatio'] : '',
	'--lifecoachhub-shop-image-fit'          => 'contain' === ( $attributes['imageFit'] ?? 'cover' ) ? 'contain' : '',
);

$style = '';
foreach ( $style_vars as $property => $value ) {
	if ( '' !== $value ) {
		$style .= $property . ':' . $value . ';';
	}
}

// Tag links so sales from this site show up in Life Coach Hub attribution.
$tracking = array(
	'utm_source'   => 'wordpress',
	'utm_medium'   => 'lifecoachhub_plugin',
	'utm_campaign' => 'shop_block',
);

$present_types = array_values( array_intersect( array_keys( $type_labels ), array_unique( wp_list_pluck( $offers, 'type' ) ) ) );
$show_filters  = ! empty( $attributes['showFilters'] ) && count( $present_types ) > 1;
$show_search   = ! empty( $attributes['showSearch'] );
$show_sort     = ! empty( $attributes['showSort'] ) && count( $offers ) > 1;

// Manual order is only meaningful for hand-picked offers.
if ( 'manual' === $default_sort && ! $manual ) {
	$default_sort = 'name';
}

$context_offers = array();
foreach ( $offers as $index => $offer ) {
	$haystack         = ( $offer['title'] ?? '' ) . ' ' . ( $offer['description'] ?? '' );
	$context_offers[] = array(
		'id'       => (string) $offer['id'],
		'type'     => (string) $offer['type'],
		'title'    => (string) ( $offer['title'] ?? '' ),
		'price'    => (float) ( $offer['price']['amount'] ?? 0 ),
		'index'    => $index,
		'haystack' => function_exists( 'mb_strtolower' ) ? mb_strtolower( $haystack ) : strtolower( $haystack ),
	);
}

wp_interactivity_state(
	'lifecoachhub/shop',
	array(
		'isTabActive'  => function () {
			$context = wp_interactivity_get_context();
			return isset( $context['tab'] ) && $context['activeType'] === $context['tab'];
		},
		'isCardHidden' => function () {
			return false;
		},
		'cardOrder'    => function () {
			$context = wp_interactivity_get_context();
			$offers  = $context['offers'];
			usort(
				$offers,
				function ( $a, $b ) use ( $context ) {
					if ( 'price-asc' === $context['sort'] ) {
						return ( $a['price'] <=> $b['price'] ) ?: ( $a['index'] <=> $b['index'] );
					}
					if ( 'price-desc' === $context['sort'] ) {
						return ( $b['price'] <=> $a['price'] ) ?: ( $a['index'] <=> $b['index'] );
					}
					if ( 'name' === $context['sort'] ) {
						return strcasecmp( $a['title'], $b['title'] ) ?: ( $a['index'] <=> $b['index'] );
					}
					return $a['index'] <=> $b['index'];
				}
			);
			foreach ( $offers as $position => $offer ) {
				if ( $offer['id'] === ( $context['offerId'] ?? '' ) ) {
					return (string) $position;
				}
			}
			return '0';
		},
		'hasNoResults' => function () {
			return false;
		},
	)
);

$sort_options = array(
	'name'       => __( 'Name', 'lifecoachhub' ),
	'price-asc'  => __( 'Price: low to high', 'lifecoachhub' ),
	'price-desc' => __( 'Price: high to low', 'lifecoachhub' ),
);
if ( $manual ) {
	$sort_options = array( 'manual' => __( 'Featured', 'lifecoachhub' ) ) + $sort_options;
}

$cadence_labels = array(
	'monthly' => __( '/ month', 'lifecoachhub' ),
	'yearly'  => __( '/ year', 'lifecoachhub' ),
);

$details_class = 'button' === ( $attributes['detailsStyle'] ?? 'link' )
	? 'wp-element-button lifecoachhub-shop__details lifecoachhub-shop__details--button'
	: 'lifecoachhub-shop__details';
$link_attr     = $new_tab ? ' target="_blank" rel="noopener"' : '';

$wrapper_classes = array( 'lifecoachhub-shop' );
if ( in_array( $attributes['cardShadow'] ?? 'none', array( 'soft', 'medium', 'strong' ), true ) ) {
	$wrapper_classes[] = 'has-card-shadow-' . $attributes['cardShadow'];
}
foreach ( array( 'has-button-bg' => '--lifecoachhub-shop-button-bg', 'has-button-text' => '--lifecoachhub-shop-button-text', 'has-button-radius' => '--lifecoachhub-shop-button-radius' ) as $class => $property ) {
	if ( '' !== $style_vars[ $property ] ) {
		$wrapper_classes[] = $class;
	}
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'               => implode( ' ', $wrapper_classes ),
		'style'               => $style,
		'data-wp-interactive' => 'lifecoachhub/shop',
	)
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?> <?php echo wp_interactivity_data_wp_context( array( 'activeType' => 'all', 'search' => '', 'sort' => $default_sort, 'offers' => $context_offers ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>>
	<?php if ( $show_filters || $show_search || $show_sort ) : ?>
		<div class="lifecoachhub-shop__toolbar">
			<?php if ( $show_filters ) : ?>
				<div class="lifecoachhub-shop__filters" role="group" aria-label="<?php esc_attr_e( 'Filter offers', 'lifecoachhub' ); ?>">
					<?php foreach ( array_merge( array( 'all' ), $present_types ) as $tab ) : ?>
						<button
							type="button"
							class="lifecoachhub-shop__filter"
							<?php echo wp_interactivity_data_wp_context( array( 'tab' => $tab ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
							data-wp-on--click="actions.setType"
							data-wp-class--is-active="state.isTabActive"
							data-wp-bind--aria-pressed="state.isTabActive"
						><?php echo esc_html( 'all' === $tab ? $all_label : $type_labels[ $tab ] ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $show_search || $show_sort ) : ?>
				<div class="lifecoachhub-shop__controls">
					<?php if ( $show_search ) : ?>
						<input
							type="search"
							class="lifecoachhub-shop__search"
							placeholder="<?php esc_attr_e( 'Search offers', 'lifecoachhub' ); ?>"
							aria-label="<?php esc_attr_e( 'Search offers', 'lifecoachhub' ); ?>"
							data-wp-on--input="actions.setSearch"
						/>
					<?php endif; ?>
					<?php if ( $show_sort ) : ?>
						<select class="lifecoachhub-shop__sort" aria-label="<?php esc_attr_e( 'Sort offers', 'lifecoachhub' ); ?>" data-wp-on--change="actions.setSort">
							<?php foreach ( $sort_options as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $default_sort, $value ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<ul class="lifecoachhub-shop__grid" role="list">
		<?php foreach ( $offers as $offer ) : ?>
			<?php
			$price    = isset( $offer['price'] ) && is_array( $offer['price'] ) ? $offer['price'] : array();
			$is_free  = ! empty( $price['is_free'] );
			$sold_out = ! empty( $offer['sold_out'] );
			?>
			<li
				class="lifecoachhub-shop__card"
				<?php echo wp_interactivity_data_wp_context( array( 'offerId' => (string) $offer['id'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
				data-wp-bind--hidden="state.isCardHidden"
				data-wp-style--order="state.cardOrder"
			>
				<?php if ( ! empty( $attributes['showImage'] ) && ! empty( $offer['image_url'] ) ) : ?>
					<div class="lifecoachhub-shop__media">
						<img src="<?php echo esc_url( $offer['image_url'] ); ?>" alt="" loading="lazy" decoding="async" />
					</div>
				<?php endif; ?>

				<div class="lifecoachhub-shop__body">
					<?php if ( ! empty( $attributes['showTypeLabel'] ) ) : ?>
						<span class="lifecoachhub-shop__type"><?php echo esc_html( $type_labels[ $offer['type'] ] ); ?></span>
					<?php endif; ?>
					<<?php echo esc_html( $heading_tag ); ?> class="lifecoachhub-shop__title"><?php echo esc_html( $offer['title'] ?? '' ); ?></<?php echo esc_html( $heading_tag ); ?>>

					<?php if ( ! empty( $attributes['showDescription'] ) && ! empty( $offer['description'] ) ) : ?>
						<p class="lifecoachhub-shop__description"><?php echo esc_html( $offer['description'] ); ?></p>
					<?php endif; ?>

					<div class="lifecoachhub-shop__footer">
						<?php if ( ! empty( $attributes['showPrice'] ) ) : ?>
							<p class="lifecoachhub-shop__price">
								<?php if ( $is_free ) : ?>
									<strong><?php esc_html_e( 'Free', 'lifecoachhub' ); ?></strong>
								<?php else : ?>
									<strong><?php echo esc_html( $price['formatted'] ?? '' ); ?></strong>
									<?php if ( ! empty( $price['cadence'] ) && isset( $cadence_labels[ $price['cadence'] ] ) ) : ?>
										<span class="lifecoachhub-shop__cadence"><?php echo esc_html( $cadence_labels[ $price['cadence'] ] ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $price['original_formatted'] ) ) : ?>
										<del class="lifecoachhub-shop__original"><?php echo esc_html( $price['original_formatted'] ); ?></del>
									<?php endif; ?>
								<?php endif; ?>
							</p>
						<?php endif; ?>

						<div class="lifecoachhub-shop__actions">
							<?php if ( $sold_out ) : ?>
								<span class="lifecoachhub-shop__sold-out"><?php esc_html_e( 'Sold out', 'lifecoachhub' ); ?></span>
							<?php else : ?>
								<a class="wp-element-button lifecoachhub-shop__buy" href="<?php echo esc_url( add_query_arg( $tracking, $offer['checkout_url'] ) ); ?>"<?php echo $link_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static string. ?>><?php echo esc_html( $buy_label ); ?></a>
							<?php endif; ?>
							<?php if ( ! empty( $offer['details_url'] ) ) : ?>
								<a class="<?php echo esc_attr( $details_class ); ?>" href="<?php echo esc_url( add_query_arg( $tracking, $offer['details_url'] ) ); ?>"<?php echo $link_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static string. ?>><?php echo esc_html( $details_label ); ?></a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>

	<p class="lifecoachhub-shop__empty" hidden data-wp-bind--hidden="!state.hasNoResults"><?php esc_html_e( 'No offers match your search.', 'lifecoachhub' ); ?></p>
</div>
