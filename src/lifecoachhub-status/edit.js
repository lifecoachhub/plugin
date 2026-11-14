/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * WordPress dependencies.
 */
import { Button, PanelBody, TextControl } from '@wordpress/components';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Updates block attributes.
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const {
		label_connected: labelConnected,
		label_not_connected: labelNotConnected,
	} = attributes;
	const isConnected = true; // This is just for editor preview purpose. Actual value will be rendered from server.

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Labels', 'lifecoachhub' ) }>
					<div
						style={ {
							display: 'flex',
							flexDirection: 'column',
							gap: '12px',
						} }
					>
						<TextControl
							label={ __(
								'Label when connected',
								'lifecoachhub'
							) }
							value={ labelConnected }
							onChange={ ( value ) =>
								setAttributes( { label_connected: value } )
							}
							placeholder={ __( 'Connected', 'lifecoachhub' ) }
							__next40pxDefaultSize
							__nextHasNoMarginBottom
						/>

						<TextControl
							label={ __(
								'Label when not connected',
								'lifecoachhub'
							) }
							value={ labelNotConnected }
							onChange={ ( value ) =>
								setAttributes( { label_not_connected: value } )
							}
							placeholder={ __(
								'Not Connected',
								'lifecoachhub'
							) }
							__next40pxDefaultSize
							__nextHasNoMarginBottom
						/>

						<div>
							<Button
								variant="secondary"
								size="compact"
								onClick={ () =>
									setAttributes( {
										label_connected: __(
											'Connected',
											'lifecoachhub'
										),
										label_not_connected: __(
											'Not Connected',
											'lifecoachhub'
										),
									} )
								}
							>
								{ __( 'Reset labels', 'lifecoachhub' ) }
							</Button>
						</div>
					</div>
				</PanelBody>
			</InspectorControls>

			<p { ...useBlockProps() }>
				<span>
					{ isConnected
						? labelConnected || __( 'Connected', 'lifecoachhub' )
						: labelNotConnected ||
						  __( 'Not Connected', 'lifecoachhub' ) }
				</span>
				<span>{ isConnected ? '✅' : '❌' }</span>
			</p>
		</>
	);
}
