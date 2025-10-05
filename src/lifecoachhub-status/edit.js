/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * WordPress dependencies.
 */
import {
	__experimentalToolsPanel as ToolsPanel,
	TextControl,
} from '@wordpress/components';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {
	InspectorControls,
	useBlockProps,
	__experimentalGetElementClassName,
} from '@wordpress/block-editor';
import { useToolsPanelDropdownMenuProps } from '../hooks/useToolsPanelDropdownMenuProps';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const { label_connected, label_not_connected } = attributes;
	const isConnected = true; // This is just for editor preview purpose. Actual value will be rendered from server.
	const dropdownMenuProps = useToolsPanelDropdownMenuProps();

	return (
		<>
			<InspectorControls>
				<ToolsPanel
					label={__('Labels', 'lifecoachhub')}
					resetAll={() =>
						setAttributes({
							label_connected: __('Connected', 'lifecoachhub'),
							label_not_connected: __('Not Connected', 'lifecoachhub'),
						})
					}
					dropdownMenuProps={dropdownMenuProps}
					style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}
				>
					<TextControl
						label={__('Label when connected', 'lifecoachhub')}
						value={label_connected}
						onChange={(label_connected) =>
							setAttributes({ label_connected })
						}
						placeholder={__('Connected', 'lifecoachhub')}
						isBlock
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>

					<TextControl
						label={__('Label when not connected', 'lifecoachhub')}
						value={label_not_connected}
						onChange={(label_not_connected) =>
							setAttributes({ label_not_connected })
						}
						placeholder={__('Not Connected', 'lifecoachhub')}
						isBlock
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
				</ToolsPanel>
			</InspectorControls>

			<p {...useBlockProps()}>
				{isConnected ? label_connected || __('Connected', 'lifecoachhub') : label_not_connected || __('Not Connected', 'lifecoachhub')}
			</p>
		</>
	);
}
