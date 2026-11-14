<?php
/**
 * Block patterns built around the Life Coach Hub Shop block.
 *
 * @package LifeCoachHub\Patterns
 */

namespace LifeCoachHub\Patterns;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the "Life Coach Hub" pattern category and its patterns.
 */
class LifeCoachHub_Patterns {

	const CATEGORY = 'lifecoachhub';

	/**
	 * Patterns whose look needs the section around the block, so they are not offered in the block's Layout picker.
	 */
	const SECTION_ONLY_LAYOUTS = array( 'dark-showcase' );

	/**
	 * Register the category and every pattern.
	 */
	public static function register() {
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		register_block_pattern_category(
			self::CATEGORY,
			array(
				'label'       => __( 'Life Coach Hub', 'lifecoachhub' ),
				'description' => __( 'Shop layouts that show your Life Coach Hub offers.', 'lifecoachhub' ),
			)
		);

		foreach ( self::patterns() as $name => $pattern ) {
			register_block_pattern(
				'lifecoachhub/' . $name,
				array_merge(
					array(
						'categories' => array( self::CATEGORY ),
						'blockTypes' => array( 'lifecoachhub/shop-grid' ),
					),
					$pattern
				)
			);
		}
	}

	/**
	 * Pattern definitions.
	 *
	 * @return array<string, array{title: string, description: string, keywords: string[], content: string}>
	 */
	private static function patterns() {
		return array(
			'shop-page'        => array(
				'title'       => __( 'Coaching shop page', 'lifecoachhub' ),
				'description' => __( 'A heading, a short intro and all your offers with filters, search and sorting.', 'lifecoachhub' ),
				'keywords'    => array( 'shop', 'store', 'offers', 'coaching' ),
				'content'     => self::section(
					self::heading( __( 'Work with me', 'lifecoachhub' ), 1 )
					. self::paragraph( __( 'Choose the coaching that fits where you are right now.', 'lifecoachhub' ) )
					. self::shop( self::preset_attributes( 'shop-page' ) )
				),
			),

			'pricing-table'    => array(
				'title'       => __( 'Pricing table', 'lifecoachhub' ),
				'description' => __( 'Your offers side by side, cheapest first, with clear buy buttons. No filters.', 'lifecoachhub' ),
				'keywords'    => array( 'pricing', 'prices', 'plans', 'packages' ),
				'content'     => self::section(
					self::heading( __( 'Simple, honest pricing', 'lifecoachhub' ) )
					. self::paragraph( __( 'Pick the level of support you need. You can change any time.', 'lifecoachhub' ) )
					. self::shop( self::preset_attributes( 'pricing-table' ) )
				),
			),

			'featured-offer'   => array(
				'title'       => __( 'Featured offer spotlight', 'lifecoachhub' ),
				'description' => __( 'Your story on one side and one offer on the other. Hand-pick the offer in the block settings.', 'lifecoachhub' ),
				'keywords'    => array( 'featured', 'hero', 'spotlight', 'landing' ),
				'content'     => '<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">'
					. self::heading( __( 'Ready for your next chapter?', 'lifecoachhub' ), 2, 'left' )
					. self::paragraph( __( 'In our work together you get clarity on what matters, a plan you believe in and someone in your corner while you do it.', 'lifecoachhub' ), 'left' )
					. self::checklist(
						array(
							__( 'A clear plan for the next 90 days', 'lifecoachhub' ),
							__( 'Honest, kind accountability', 'lifecoachhub' ),
							__( 'Tools you keep using after we finish', 'lifecoachhub' ),
						)
					)
					. '</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%">'
					. self::shop( self::preset_attributes( 'featured-offer' ) )
					. '</div>
<!-- /wp:column --></div>
<!-- /wp:columns -->',
			),

			'sessions-list'    => array(
				'title'       => __( 'Book a session list', 'lifecoachhub' ),
				'description' => __( 'Your 1:1 sessions as a clean list, one per row.', 'lifecoachhub' ),
				'keywords'    => array( 'sessions', 'book', 'appointments', 'list' ),
				'content'     => self::section(
					self::heading( __( 'Book a session', 'lifecoachhub' ) )
					. self::paragraph( __( 'Choose a session and pick a time that suits you.', 'lifecoachhub' ) )
					. self::shop( self::preset_attributes( 'sessions-list' ) ),
					'default'
				),
			),

			'programs'         => array(
				'title'       => __( 'Programs and memberships', 'lifecoachhub' ),
				'description' => __( 'Group programs and memberships in two wide columns.', 'lifecoachhub' ),
				'keywords'    => array( 'membership', 'group', 'program', 'community' ),
				'content'     => self::section(
					self::heading( __( 'Grow with others', 'lifecoachhub' ) )
					. self::paragraph( __( 'Join a group program or become a member for ongoing support.', 'lifecoachhub' ) )
					. self::shop( self::preset_attributes( 'programs' ) )
				),
			),

			'digital-shop'     => array(
				'title'       => __( 'Courses and digital products', 'lifecoachhub' ),
				'description' => __( 'Courses and downloads in a four-column grid with square images.', 'lifecoachhub' ),
				'keywords'    => array( 'courses', 'digital', 'downloads', 'products' ),
				'content'     => self::section(
					self::heading( __( 'Learn at your own pace', 'lifecoachhub' ) )
					. self::paragraph( __( 'Courses, workbooks and guides you can start today.', 'lifecoachhub' ) )
					. self::shop( self::preset_attributes( 'digital-shop' ) )
				),
			),

			'minimal-list'     => array(
				'title'       => __( 'Minimal offer list', 'lifecoachhub' ),
				'description' => __( 'Offers as simple text in two columns, for a calm, editorial look.', 'lifecoachhub' ),
				'keywords'    => array( 'minimal', 'simple', 'text', 'clean' ),
				'content'     => self::section(
					self::heading( __( 'Ways to work together', 'lifecoachhub' ) )
					. self::shop( self::preset_attributes( 'minimal-list' ) ),
					'default'
				),
			),

			'dark-showcase'    => array(
				'title'       => __( 'Dark showcase section', 'lifecoachhub' ),
				'description' => __( 'A full-width dark band with light cards. Uses your theme\'s contrast colors.', 'lifecoachhub' ),
				'keywords'    => array( 'dark', 'contrast', 'section', 'showcase' ),
				'content'     => '<!-- wp:group {"align":"full","backgroundColor":"contrast","textColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-color has-contrast-background-color has-text-color has-background">'
					. self::spacer()
					. self::heading( __( 'Coaching that changes things', 'lifecoachhub' ) )
					. self::paragraph( __( 'Real support, real progress. Start with the option that fits you.', 'lifecoachhub' ) )
					. self::shop( self::preset_attributes( 'dark-showcase' ) )
					. self::spacer()
					. '</div>
<!-- /wp:group -->',
			),
		);
	}

	/**
	 * Shop block settings for each layout. Patterns and the editor's Layout picker both use these.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function preset_attributes_map() {
		return array(
			'shop-page' => array( 'align' => 'wide' ),
			'pricing-table' => array(
				'align'        => 'wide',
				'showFilters'  => false,
				'showSearch'   => false,
				'showSort'     => false,
				'showImage'    => false,
				'defaultSort'  => 'price-asc',
				'detailsStyle' => 'button',
				'buyLabel'     => __( 'Get started', 'lifecoachhub' ),
				'cardRadius'   => '16px',
				'cardPadding'  => '32px',
				'cardShadow'   => 'soft',
				'priceFontSize' => '2rem',
			),
			'featured-offer' => array(
				'columns'      => 1,
				'limit'        => 1,
				'showFilters'  => false,
				'showSearch'   => false,
				'showSort'     => false,
				'buyLabel'     => __( 'Book now', 'lifecoachhub' ),
				'cardShadow'   => 'medium',
				'cardRadius'   => '20px',
				'cardPadding'  => '28px',
				'titleFontSize' => '1.5rem',
			),
			'sessions-list' => array(
				'className'     => 'is-style-list',
				'offerTypes'    => array( 'session_type' ),
				'showFilters'   => false,
				'showSearch'    => false,
				'showSort'      => false,
				'showTypeLabel' => false,
				'buyLabel'      => __( 'Book', 'lifecoachhub' ),
				'imageAspectRatio' => '1',
			),
			'programs' => array(
				'align'        => 'wide',
				'offerTypes'   => array( 'group_coaching_offer', 'coach_membership' ),
				'columns'      => 2,
				'showSearch'   => false,
				'showSort'     => false,
				'buyLabel'     => __( 'Join now', 'lifecoachhub' ),
				'cardRadius'   => '20px',
				'cardPadding'  => '28px',
			),
			'digital-shop' => array(
				'align'            => 'wide',
				'offerTypes'       => array( 'coach_course', 'digital_product', 'coach_offer' ),
				'columns'          => 4,
				'imageAspectRatio' => '1',
				'showDescription'  => false,
				'showSort'         => false,
				'buyLabel'         => __( 'Get it', 'lifecoachhub' ),
			),
			'minimal-list' => array(
				'className'     => 'is-style-minimal',
				'columns'       => 2,
				'showImage'     => false,
				'showFilters'   => false,
				'showSearch'    => false,
				'showSort'      => false,
				'showTypeLabel' => false,
				'buyLabel'      => __( 'Choose', 'lifecoachhub' ),
			),
			'dark-showcase' => array(
				'align'                  => 'wide',
				'showSearch'             => false,
				'cardRadius'             => '16px',
				'cardPadding'            => '28px',
				'buttonBackground'       => '#ffffff',
				'buttonTextColor'        => '#111111',
				'filterActiveBackground' => '#ffffff',
				'filterActiveTextColor'  => '#111111',
			),
		);
	}

	/**
	 * Shop block settings for one layout.
	 *
	 * @param string $name Layout name.
	 * @return array<string, mixed>
	 */
	private static function preset_attributes( $name ) {
		$map = self::preset_attributes_map();
		return $map[ $name ] ?? array();
	}

	/**
	 * Layouts for the editor's Layout picker: name, title, description and Shop block settings.
	 *
	 * @return array<int, array{name: string, title: string, description: string, attributes: array<string, mixed>}>
	 */
	public static function editor_layouts() {
		$layouts = array();
		foreach ( self::patterns() as $name => $pattern ) {
			// This look depends on its dark section, so it is offered as a pattern only.
			if ( in_array( $name, self::SECTION_ONLY_LAYOUTS, true ) ) {
				continue;
			}

			$layouts[] = array(
				'name'        => $name,
				'title'       => $pattern['title'],
				'description' => $pattern['description'],
				'attributes'  => self::preset_attributes( $name ),
			);
		}

		return $layouts;
	}

	/**
	 * A constrained section wrapping the given blocks.
	 *
	 * @param string $inner Inner block markup.
	 * @param string $align 'wide' or 'default'.
	 * @return string
	 */
	private static function section( $inner, $align = 'wide' ) {
		if ( 'wide' === $align ) {
			return '<!-- wp:group {"align":"wide","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide">' . $inner . '</div>
<!-- /wp:group -->';
		}

		return '<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">' . $inner . '</div>
<!-- /wp:group -->';
	}

	/**
	 * Heading block.
	 *
	 * @param string $text  Heading text.
	 * @param int    $level Heading level.
	 * @param string $align 'center' or 'left'.
	 * @return string
	 */
	private static function heading( $text, $level = 2, $align = 'center' ) {
		$attrs = array();
		if ( 'center' === $align ) {
			$attrs['textAlign'] = 'center';
		}
		if ( 2 !== $level ) {
			$attrs['level'] = $level;
		}

		$class = 'wp-block-heading' . ( 'center' === $align ? ' has-text-align-center' : '' );

		return "\n\n" . '<!-- wp:heading' . ( $attrs ? ' ' . wp_json_encode( $attrs ) : '' ) . ' -->
<h' . $level . ' class="' . $class . '">' . esc_html( $text ) . '</h' . $level . '>
<!-- /wp:heading -->' . "\n\n";
	}

	/**
	 * Paragraph block.
	 *
	 * @param string $text  Paragraph text.
	 * @param string $align 'center' or 'left'.
	 * @return string
	 */
	private static function paragraph( $text, $align = 'center' ) {
		if ( 'center' === $align ) {
			return '<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html( $text ) . '</p>
<!-- /wp:paragraph -->' . "\n\n";
		}

		return '<!-- wp:paragraph -->
<p>' . esc_html( $text ) . '</p>
<!-- /wp:paragraph -->' . "\n\n";
	}

	/**
	 * List block with one item per line.
	 *
	 * @param string[] $items List items.
	 * @return string
	 */
	private static function checklist( array $items ) {
		$markup = '<!-- wp:list -->
<ul class="wp-block-list">';
		foreach ( $items as $item ) {
			$markup .= '<!-- wp:list-item -->
<li>' . esc_html( $item ) . '</li>
<!-- /wp:list-item -->';
		}

		return $markup . '</ul>
<!-- /wp:list -->' . "\n\n";
	}

	/**
	 * Spacer block.
	 *
	 * @return string
	 */
	private static function spacer() {
		return '<!-- wp:spacer {"height":"48px"} -->
<div style="height:48px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->' . "\n\n";
	}

	/**
	 * Shop block with the given attributes.
	 *
	 * @param array<string, mixed> $attrs Block attributes.
	 * @return string
	 */
	private static function shop( array $attrs = array() ) {
		return '<!-- wp:lifecoachhub/shop-grid' . ( $attrs ? ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_UNICODE ) : '' ) . ' /-->' . "\n";
	}
}
