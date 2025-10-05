/**
 * WordPress dependencies.
 */
import { useViewportMatch } from '@wordpress/compose';

/**
 * React hook to provide props for the dropdown menu of the ToolsPanel component.
 *
 * @returns {Object} Props for the dropdown menu of the ToolsPanel.
 */
export function useToolsPanelDropdownMenuProps() {
	const isMobile = useViewportMatch('medium', '<');
	return !isMobile
		? {
			popoverProps: {
				placement: 'left-start',
				offset: 259,// For non-mobile, inner sidebar width (248px) - button width (24px) - border (1px) + padding (16px) + spacing (20px)
			},
		}
		: {};
}