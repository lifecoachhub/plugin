<?php
// This file is generated. Do not modify it manually.
return array(
	'lifecoachhub-status' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'lifecoachhub/lifecoachhub-status',
		'version' => '0.1.0',
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
	)
);
