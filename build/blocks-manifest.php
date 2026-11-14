<?php
// This file is generated. Do not modify it manually.
return array(
	'lifecoachhub-status' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'lifecoachhub/lifecoachhub-status',
		'version' => '0.2.0',
		'title' => 'Life Coach Hub Status',
		'category' => 'widgets',
		'icon' => 'smiley',
		'keywords' => array(
			'lifecoachhub',
			'status',
			'life coach hub'
		),
		'description' => 'Get the current connection status of your Life Coach Hub account connected or not.',
		'attributes' => array(
			'label_connected' => array(
				'type' => 'string'
			),
			'label_not_connected' => array(
				'type' => 'string'
			)
		),
		'example' => array(
			
		),
		'supports' => array(
			'interactivity' => true,
			'html' => false,
			'anchor' => true,
			'color' => array(
				'gradients' => true,
				'background' => true,
				'text' => true
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true,
				'fontFamily' => true,
				'fontWeight' => true,
				'textTransform' => true,
				'letterSpacing' => true
			),
			'layout' => true
		),
		'textdomain' => 'lifecoachhub',
		'editorScript' => 'file:./index.js',
		'style' => 'file:./style-index.css',
		'render' => 'file:./view.php'
	),
	'shop-grid' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'lifecoachhub/shop-grid',
		'version' => '0.2.0',
		'title' => 'Life Coach Hub Shop',
		'category' => 'widgets',
		'icon' => 'store',
		'description' => 'Show your Life Coach Hub offers as a shop, with filters, search and buy buttons.',
		'keywords' => array(
			'shop',
			'store',
			'offers',
			'coaching',
			'life coach hub'
		),
		'textdomain' => 'lifecoachhub',
		'attributes' => array(
			'offerTypes' => array(
				'type' => 'array',
				'items' => array(
					'type' => 'string'
				),
				'default' => array(
					
				)
			),
			'columns' => array(
				'type' => 'number',
				'default' => 3
			),
			'limit' => array(
				'type' => 'number',
				'default' => 0
			),
			'showFilters' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showSearch' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showSort' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showImage' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showDescription' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showPrice' => array(
				'type' => 'boolean',
				'default' => true
			),
			'buyLabel' => array(
				'type' => 'string',
				'default' => ''
			),
			'detailsLabel' => array(
				'type' => 'string',
				'default' => ''
			),
			'openInNewTab' => array(
				'type' => 'boolean',
				'default' => false
			),
			'selectionMode' => array(
				'type' => 'string',
				'enum' => array(
					'auto',
					'manual'
				),
				'default' => 'auto'
			),
			'selectedOffers' => array(
				'type' => 'array',
				'items' => array(
					'type' => 'string'
				),
				'default' => array(
					
				)
			),
			'defaultSort' => array(
				'type' => 'string',
				'enum' => array(
					'name',
					'price-asc',
					'price-desc',
					'manual'
				),
				'default' => 'name'
			),
			'filterLabels' => array(
				'type' => 'object',
				'default' => array(
					
				)
			),
			'showTypeLabel' => array(
				'type' => 'boolean',
				'default' => true
			),
			'headingLevel' => array(
				'type' => 'number',
				'default' => 3
			),
			'imageAspectRatio' => array(
				'type' => 'string',
				'default' => '16/9'
			),
			'imageFit' => array(
				'type' => 'string',
				'enum' => array(
					'cover',
					'contain'
				),
				'default' => 'cover'
			),
			'detailsStyle' => array(
				'type' => 'string',
				'enum' => array(
					'link',
					'button'
				),
				'default' => 'link'
			),
			'cardBackground' => array(
				'type' => 'string',
				'default' => ''
			),
			'cardBorderColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'cardBorderWidth' => array(
				'type' => 'string',
				'default' => ''
			),
			'cardRadius' => array(
				'type' => 'string',
				'default' => ''
			),
			'cardPadding' => array(
				'type' => 'string',
				'default' => ''
			),
			'cardShadow' => array(
				'type' => 'string',
				'enum' => array(
					'none',
					'soft',
					'medium',
					'strong'
				),
				'default' => 'none'
			),
			'buttonBackground' => array(
				'type' => 'string',
				'default' => ''
			),
			'buttonTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'buttonRadius' => array(
				'type' => 'string',
				'default' => ''
			),
			'filterActiveBackground' => array(
				'type' => 'string',
				'default' => ''
			),
			'filterActiveTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'titleFontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'priceFontSize' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'interactivity' => true,
			'color' => array(
				'background' => true,
				'text' => true,
				'link' => true
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true,
				'blockGap' => true
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true,
				'__experimentalFontFamily' => true,
				'__experimentalFontWeight' => true,
				'__experimentalLetterSpacing' => true
			),
			'__experimentalBorder' => array(
				'color' => true,
				'radius' => true,
				'style' => true,
				'width' => true
			),
			'shadow' => true,
			'dimensions' => array(
				'minHeight' => true
			)
		),
		'styles' => array(
			array(
				'name' => 'cards',
				'label' => 'Cards',
				'isDefault' => true
			),
			array(
				'name' => 'list',
				'label' => 'List'
			),
			array(
				'name' => 'minimal',
				'label' => 'Minimal'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScriptModule' => 'file:./view.js',
		'render' => 'file:./render.php'
	)
);
