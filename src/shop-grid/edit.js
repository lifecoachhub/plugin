/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import {
	BlockControls,
	InspectorControls,
	PanelColorSettings,
	useBlockProps,
	useSettings,
} from '@wordpress/block-editor';
import {
	Button,
	CheckboxControl,
	FontSizePicker,
	PanelBody,
	Placeholder,
	RangeControl,
	SelectControl,
	Spinner,
	TextControl,
	ToggleControl,
	ToolbarButton,
	RadioControl,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import { TYPE_LABELS, CADENCE_LABELS } from './constants';
import { ASPECT_RATIOS, chooseOffers, styleVars } from './style-vars';
import LayoutPicker, { getLayouts } from './layout-picker';
import './editor.scss';

const STORE_PATH = '/lifecoachhub/v1/store';
const BLOCK_NAME = 'lifecoachhub/shop-grid';

// One request shared by every Shop block and layout preview on the page.
let storeRequest = null;

/**
 * Fetch the store, reusing the in-flight or finished request unless a refresh is asked for.
 *
 * @param {boolean} refresh Force a fresh copy from Life Coach Hub.
 * @return {Promise<Object>} REST response.
 */
function fetchStore( refresh = false ) {
	if ( refresh || ! storeRequest ) {
		storeRequest = apiFetch( {
			path: refresh ? `${ STORE_PATH }?refresh=1` : STORE_PATH,
		} ).catch( ( fetchError ) => {
			storeRequest = null;
			throw fetchError;
		} );
	}
	return storeRequest;
}

/**
 * Offers in the block's default sort order, as the frontend shows them on load.
 *
 * @param {Array}  offers Offers to show.
 * @param {string} sort   Default sort.
 * @return {Array} Sorted offers.
 */
function sortForPreview( offers, sort ) {
	const sorted = [ ...offers ];
	if ( sort === 'price-asc' ) {
		sorted.sort( ( a, b ) => a.price.amount - b.price.amount );
	} else if ( sort === 'price-desc' ) {
		sorted.sort( ( a, b ) => b.price.amount - a.price.amount );
	} else if ( sort === 'name' ) {
		sorted.sort( ( a, b ) =>
			a.title.localeCompare( b.title, undefined, { sensitivity: 'base' } )
		);
	}
	return sorted;
}

const ASPECT_LABELS = {
	auto: __( 'Original', 'lifecoachhub' ),
	1: __( 'Square', 'lifecoachhub' ),
};

const SORT_LABELS = {
	manual: __( 'Featured', 'lifecoachhub' ),
	name: __( 'Name', 'lifecoachhub' ),
	'price-asc': __( 'Price: low to high', 'lifecoachhub' ),
	'price-desc': __( 'Price: high to low', 'lifecoachhub' ),
};

/**
 * A pixel slider that stores values like "12px" and clears to "" (theme default) on reset.
 *
 * @param {Object}   props          Props.
 * @param {string}   props.label    Label.
 * @param {string}   props.value    Stored value, e.g. "12px".
 * @param {number}   props.max      Maximum pixels.
 * @param {Function} props.onChange Receives the new value.
 * @return {Element} Control.
 */
function PxRangeControl( { label, value, max, onChange } ) {
	const number = parseFloat( value );

	return (
		<RangeControl
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			label={ label }
			value={ Number.isNaN( number ) ? undefined : number }
			min={ 0 }
			max={ max }
			allowReset
			onChange={ ( next ) =>
				onChange( next === undefined ? '' : `${ next }px` )
			}
		/>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		selectionMode,
		selectedOffers,
		offerTypes,
		columns,
		limit,
		showFilters,
		showSearch,
		showSort,
		defaultSort,
		filterLabels,
		showImage,
		imageAspectRatio,
		imageFit,
		showTypeLabel,
		showDescription,
		showPrice,
		headingLevel,
		buyLabel,
		detailsLabel,
		detailsStyle,
		openInNewTab,
		cardBackground,
		cardBorderColor,
		cardBorderWidth,
		cardRadius,
		cardPadding,
		cardShadow,
		buttonBackground,
		buttonTextColor,
		buttonRadius,
		filterActiveBackground,
		filterActiveTextColor,
		titleFontSize,
		priceFontSize,
	} = attributes;

	const [ store, setStore ] = useState( null );
	const [ error, setError ] = useState( null );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ isPickerOpen, setIsPickerOpen ] = useState( false );
	const [ fontSizes ] = useSettings( 'typography.fontSizes' );

	const loadStore = ( refresh = false ) => {
		setIsLoading( true );
		setError( null );
		fetchStore( refresh )
			.then( ( response ) => setStore( response ) )
			.catch( ( fetchError ) => setError( fetchError ) )
			.finally( () => setIsLoading( false ) );
	};

	useEffect( () => {
		loadStore();
	}, [] );

	const isManual = selectionMode === 'manual';
	const labels = Object.fromEntries(
		Object.entries( TYPE_LABELS ).map( ( [ type, label ] ) => [
			type,
			filterLabels?.[ type ]?.trim() ? filterLabels[ type ] : label,
		] )
	);
	const allLabel = filterLabels?.all?.trim()
		? filterLabels.all
		: __( 'All', 'lifecoachhub' );

	const allOffers = ( store?.store?.offers ?? [] ).filter(
		( offer ) => TYPE_LABELS[ offer.type ]
	);
	const shown = chooseOffers( allOffers, attributes, TYPE_LABELS );
	const effectiveSort =
		defaultSort === 'manual' && ! isManual ? 'name' : defaultSort;
	const offers = sortForPreview( shown, effectiveSort );
	const presentTypes = Object.keys( TYPE_LABELS ).filter( ( type ) =>
		offers.some( ( offer ) => offer.type === type )
	);
	const HeadingTag = `h${ headingLevel }`;

	const blockProps = useBlockProps( {
		className: [
			'lifecoachhub-shop',
			buttonBackground && 'has-button-bg',
			buttonTextColor && 'has-button-text',
			buttonRadius && 'has-button-radius',
			cardShadow &&
				cardShadow !== 'none' &&
				`has-card-shadow-${ cardShadow }`,
		]
			.filter( Boolean )
			.join( ' ' ),
		style: styleVars( attributes ),
	} );

	const toggleType = ( type, checked ) =>
		setAttributes( {
			offerTypes: checked
				? [ ...offerTypes, type ]
				: offerTypes.filter( ( item ) => item !== type ),
		} );

	const moveOffer = ( id, direction ) => {
		const next = [ ...selectedOffers ];
		const from = next.indexOf( id );
		const to = from + direction;
		if ( from < 0 || to < 0 || to >= next.length ) {
			return;
		}
		[ next[ from ], next[ to ] ] = [ next[ to ], next[ from ] ];
		setAttributes( { selectedOffers: next } );
	};

	const setLabel = ( key, value ) =>
		setAttributes( { filterLabels: { ...filterLabels, [ key ]: value } } );

	const offerById = ( id ) => allOffers.find( ( offer ) => offer.id === id );

	const settingsPanels = (
		<InspectorControls>
			<PanelBody title={ __( 'Offers', 'lifecoachhub' ) }>
				<RadioControl
					label={ __( 'Choose offers', 'lifecoachhub' ) }
					selected={ selectionMode }
					options={ [
						{
							value: 'auto',
							label: __( 'By offer type', 'lifecoachhub' ),
						},
						{
							value: 'manual',
							label: __( 'Hand-pick and order', 'lifecoachhub' ),
						},
					] }
					onChange={ ( value ) =>
						setAttributes( { selectionMode: value } )
					}
				/>

				{ ! isManual && (
					<>
						<p className="components-base-control__help">
							{ __(
								'Leave all unticked to show every offer from your public store.',
								'lifecoachhub'
							) }
						</p>
						{ Object.entries( TYPE_LABELS ).map(
							( [ type, label ] ) => (
								<CheckboxControl
									key={ type }
									__nextHasNoMarginBottom
									label={ label }
									checked={ offerTypes.includes( type ) }
									onChange={ ( checked ) =>
										toggleType( type, checked )
									}
								/>
							)
						) }
					</>
				) }

				{ isManual && (
					<div className="lifecoachhub-shop-picker">
						<p className="components-base-control__help">
							{ __(
								'Tick offers to show them. Use the arrows to set their order.',
								'lifecoachhub'
							) }
						</p>
						{ selectedOffers.map( ( id, position ) => {
							const offer = offerById( id );
							return (
								<div
									key={ id }
									className="lifecoachhub-shop-picker__row is-selected"
								>
									<CheckboxControl
										__nextHasNoMarginBottom
										label={
											offer
												? offer.title
												: __(
														'Removed in Life Coach Hub',
														'lifecoachhub'
												  )
										}
										checked
										onChange={ () =>
											setAttributes( {
												selectedOffers:
													selectedOffers.filter(
														( item ) => item !== id
													),
											} )
										}
									/>
									<Button
										size="small"
										icon="arrow-up-alt2"
										label={ __(
											'Move up',
											'lifecoachhub'
										) }
										disabled={ position === 0 }
										onClick={ () => moveOffer( id, -1 ) }
									/>
									<Button
										size="small"
										icon="arrow-down-alt2"
										label={ __(
											'Move down',
											'lifecoachhub'
										) }
										disabled={
											position ===
											selectedOffers.length - 1
										}
										onClick={ () => moveOffer( id, 1 ) }
									/>
								</div>
							);
						} ) }
						{ allOffers
							.filter(
								( offer ) =>
									! selectedOffers.includes( offer.id )
							)
							.map( ( offer ) => (
								<div
									key={ offer.id }
									className="lifecoachhub-shop-picker__row"
								>
									<CheckboxControl
										__nextHasNoMarginBottom
										label={ `${ offer.title } · ${
											TYPE_LABELS[ offer.type ]
										}` }
										checked={ false }
										onChange={ () =>
											setAttributes( {
												selectedOffers: [
													...selectedOffers,
													offer.id,
												],
											} )
										}
									/>
								</div>
							) ) }
					</div>
				) }

				<RangeControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label={ __( 'Maximum offers (0 = all)', 'lifecoachhub' ) }
					value={ limit }
					min={ 0 }
					max={ 24 }
					onChange={ ( value ) =>
						setAttributes( { limit: value ?? 0 } )
					}
				/>
			</PanelBody>

			<PanelBody
				title={ __( 'Layout', 'lifecoachhub' ) }
				initialOpen={ false }
			>
				<RangeControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label={ __( 'Columns', 'lifecoachhub' ) }
					value={ columns }
					min={ 1 }
					max={ 6 }
					onChange={ ( value ) =>
						setAttributes( { columns: value ?? 3 } )
					}
				/>
				<ToggleControl
					__nextHasNoMarginBottom
					label={ __( 'Show filter tabs', 'lifecoachhub' ) }
					checked={ showFilters }
					onChange={ ( value ) =>
						setAttributes( { showFilters: value } )
					}
				/>
				<ToggleControl
					__nextHasNoMarginBottom
					label={ __( 'Show search', 'lifecoachhub' ) }
					checked={ showSearch }
					onChange={ ( value ) =>
						setAttributes( { showSearch: value } )
					}
				/>
				<ToggleControl
					__nextHasNoMarginBottom
					label={ __( 'Show sort menu', 'lifecoachhub' ) }
					checked={ showSort }
					onChange={ ( value ) =>
						setAttributes( { showSort: value } )
					}
				/>
				<SelectControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label={ __( 'Default order', 'lifecoachhub' ) }
					value={ effectiveSort }
					options={ [
						...( isManual
							? [
									{
										value: 'manual',
										label: __(
											'Featured (your order)',
											'lifecoachhub'
										),
									},
							  ]
							: [] ),
						{ value: 'name', label: __( 'Name', 'lifecoachhub' ) },
						{
							value: 'price-asc',
							label: __( 'Price: low to high', 'lifecoachhub' ),
						},
						{
							value: 'price-desc',
							label: __( 'Price: high to low', 'lifecoachhub' ),
						},
					] }
					onChange={ ( value ) =>
						setAttributes( { defaultSort: value } )
					}
				/>
			</PanelBody>

			<PanelBody
				title={ __( 'Card content', 'lifecoachhub' ) }
				initialOpen={ false }
			>
				<ToggleControl
					__nextHasNoMarginBottom
					label={ __( 'Show image', 'lifecoachhub' ) }
					checked={ showImage }
					onChange={ ( value ) =>
						setAttributes( { showImage: value } )
					}
				/>
				{ showImage && (
					<>
						<SelectControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Image shape', 'lifecoachhub' ) }
							value={ imageAspectRatio }
							options={ ASPECT_RATIOS.map( ( ratio ) => ( {
								value: ratio,
								label:
									ASPECT_LABELS[ ratio ] ??
									ratio.replace( '/', ':' ),
							} ) ) }
							onChange={ ( value ) =>
								setAttributes( { imageAspectRatio: value } )
							}
						/>
						<SelectControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Image fit', 'lifecoachhub' ) }
							value={ imageFit }
							options={ [
								{
									value: 'cover',
									label: __(
										'Fill the shape (crop)',
										'lifecoachhub'
									),
								},
								{
									value: 'contain',
									label: __(
										'Show whole image',
										'lifecoachhub'
									),
								},
							] }
							onChange={ ( value ) =>
								setAttributes( { imageFit: value } )
							}
						/>
					</>
				) }
				<ToggleControl
					__nextHasNoMarginBottom
					label={ __( 'Show offer type label', 'lifecoachhub' ) }
					checked={ showTypeLabel }
					onChange={ ( value ) =>
						setAttributes( { showTypeLabel: value } )
					}
				/>
				<ToggleControl
					__nextHasNoMarginBottom
					label={ __( 'Show description', 'lifecoachhub' ) }
					checked={ showDescription }
					onChange={ ( value ) =>
						setAttributes( { showDescription: value } )
					}
				/>
				<ToggleControl
					__nextHasNoMarginBottom
					label={ __( 'Show price', 'lifecoachhub' ) }
					checked={ showPrice }
					onChange={ ( value ) =>
						setAttributes( { showPrice: value } )
					}
				/>
				<SelectControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label={ __( 'Title heading level', 'lifecoachhub' ) }
					help={ __(
						'Pick the level that fits under the headings above the shop.',
						'lifecoachhub'
					) }
					value={ String( headingLevel ) }
					options={ [ 2, 3, 4, 5, 6 ].map( ( level ) => ( {
						value: String( level ),
						label: sprintf(
							/* translators: %d: heading level, e.g. 3. */
							__( 'Heading %d', 'lifecoachhub' ),
							level
						),
					} ) ) }
					onChange={ ( value ) =>
						setAttributes( { headingLevel: Number( value ) } )
					}
				/>
			</PanelBody>

			<PanelBody
				title={ __( 'Buttons', 'lifecoachhub' ) }
				initialOpen={ false }
			>
				<TextControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label={ __( 'Buy button text', 'lifecoachhub' ) }
					placeholder={ __( 'Buy now', 'lifecoachhub' ) }
					value={ buyLabel }
					onChange={ ( value ) =>
						setAttributes( { buyLabel: value } )
					}
				/>
				<TextControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label={ __( 'Details text', 'lifecoachhub' ) }
					placeholder={ __( 'Learn more', 'lifecoachhub' ) }
					value={ detailsLabel }
					onChange={ ( value ) =>
						setAttributes( { detailsLabel: value } )
					}
				/>
				<SelectControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label={ __( 'Show details as', 'lifecoachhub' ) }
					value={ detailsStyle }
					options={ [
						{
							value: 'link',
							label: __( 'Text link', 'lifecoachhub' ),
						},
						{
							value: 'button',
							label: __( 'Outline button', 'lifecoachhub' ),
						},
					] }
					onChange={ ( value ) =>
						setAttributes( { detailsStyle: value } )
					}
				/>
				<ToggleControl
					__nextHasNoMarginBottom
					label={ __( 'Open checkout in a new tab', 'lifecoachhub' ) }
					checked={ openInNewTab }
					onChange={ ( value ) =>
						setAttributes( { openInNewTab: value } )
					}
				/>
			</PanelBody>

			<PanelBody
				title={ __( 'Filter labels', 'lifecoachhub' ) }
				initialOpen={ false }
			>
				<p className="components-base-control__help">
					{ __(
						'Rename the filter tabs. The same names show on each card.',
						'lifecoachhub'
					) }
				</p>
				<TextControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label={ __( 'All', 'lifecoachhub' ) }
					placeholder={ __( 'All', 'lifecoachhub' ) }
					value={ filterLabels?.all ?? '' }
					onChange={ ( value ) => setLabel( 'all', value ) }
				/>
				{ Object.entries( TYPE_LABELS ).map( ( [ type, label ] ) => (
					<TextControl
						key={ type }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ label }
						placeholder={ label }
						value={ filterLabels?.[ type ] ?? '' }
						onChange={ ( value ) => setLabel( type, value ) }
					/>
				) ) }
			</PanelBody>
		</InspectorControls>
	);

	const stylePanels = (
		<InspectorControls group="styles">
			<PanelColorSettings
				title={ __( 'Card', 'lifecoachhub' ) }
				initialOpen={ false }
				colorSettings={ [
					{
						label: __( 'Background', 'lifecoachhub' ),
						value: cardBackground,
						onChange: ( value ) =>
							setAttributes( { cardBackground: value ?? '' } ),
					},
					{
						label: __( 'Border', 'lifecoachhub' ),
						value: cardBorderColor,
						onChange: ( value ) =>
							setAttributes( { cardBorderColor: value ?? '' } ),
					},
				] }
			>
				<PxRangeControl
					label={ __( 'Border width', 'lifecoachhub' ) }
					value={ cardBorderWidth }
					max={ 12 }
					onChange={ ( value ) =>
						setAttributes( { cardBorderWidth: value } )
					}
				/>
				<PxRangeControl
					label={ __( 'Corner radius', 'lifecoachhub' ) }
					value={ cardRadius }
					max={ 48 }
					onChange={ ( value ) =>
						setAttributes( { cardRadius: value } )
					}
				/>
				<PxRangeControl
					label={ __( 'Inner padding', 'lifecoachhub' ) }
					value={ cardPadding }
					max={ 64 }
					onChange={ ( value ) =>
						setAttributes( { cardPadding: value } )
					}
				/>
				<SelectControl
					__nextHasNoMarginBottom
					__next40pxDefaultSize
					label={ __( 'Shadow', 'lifecoachhub' ) }
					value={ cardShadow }
					options={ [
						{ value: 'none', label: __( 'None', 'lifecoachhub' ) },
						{ value: 'soft', label: __( 'Soft', 'lifecoachhub' ) },
						{
							value: 'medium',
							label: __( 'Medium', 'lifecoachhub' ),
						},
						{
							value: 'strong',
							label: __( 'Strong', 'lifecoachhub' ),
						},
					] }
					onChange={ ( value ) =>
						setAttributes( { cardShadow: value } )
					}
				/>
			</PanelColorSettings>

			<PanelColorSettings
				title={ __( 'Buttons', 'lifecoachhub' ) }
				initialOpen={ false }
				colorSettings={ [
					{
						label: __( 'Button background', 'lifecoachhub' ),
						value: buttonBackground,
						onChange: ( value ) =>
							setAttributes( { buttonBackground: value ?? '' } ),
					},
					{
						label: __( 'Button text', 'lifecoachhub' ),
						value: buttonTextColor,
						onChange: ( value ) =>
							setAttributes( { buttonTextColor: value ?? '' } ),
					},
				] }
			>
				<PxRangeControl
					label={ __( 'Button corner radius', 'lifecoachhub' ) }
					value={ buttonRadius }
					max={ 100 }
					onChange={ ( value ) =>
						setAttributes( { buttonRadius: value } )
					}
				/>
			</PanelColorSettings>

			<PanelColorSettings
				title={ __( 'Filter tabs', 'lifecoachhub' ) }
				initialOpen={ false }
				colorSettings={ [
					{
						label: __( 'Active tab background', 'lifecoachhub' ),
						value: filterActiveBackground,
						onChange: ( value ) =>
							setAttributes( {
								filterActiveBackground: value ?? '',
							} ),
					},
					{
						label: __( 'Active tab text', 'lifecoachhub' ),
						value: filterActiveTextColor,
						onChange: ( value ) =>
							setAttributes( {
								filterActiveTextColor: value ?? '',
							} ),
					},
				] }
			/>

			<PanelBody
				title={ __( 'Text sizes', 'lifecoachhub' ) }
				initialOpen={ false }
			>
				<p className="lifecoachhub-shop-size-label">
					{ __( 'Offer title', 'lifecoachhub' ) }
				</p>
				<FontSizePicker
					__next40pxDefaultSize
					fontSizes={ fontSizes }
					value={ titleFontSize || undefined }
					onChange={ ( value ) =>
						setAttributes( { titleFontSize: value ?? '' } )
					}
					withReset
				/>
				<p className="lifecoachhub-shop-size-label">
					{ __( 'Price', 'lifecoachhub' ) }
				</p>
				<FontSizePicker
					__next40pxDefaultSize
					fontSizes={ fontSizes }
					value={ priceFontSize || undefined }
					onChange={ ( value ) =>
						setAttributes( { priceFontSize: value ?? '' } )
					}
					withReset
				/>
			</PanelBody>
		</InspectorControls>
	);

	const inspector = (
		<>
			{ settingsPanels }
			{ stylePanels }
		</>
	);

	const toolbar = (
		<BlockControls group="other">
			{ getLayouts().length > 0 && (
				<ToolbarButton
					icon="layout"
					label={ __( 'Change layout', 'lifecoachhub' ) }
					onClick={ () => setIsPickerOpen( true ) }
				>
					{ __( 'Layout', 'lifecoachhub' ) }
				</ToolbarButton>
			) }
			<ToolbarButton
				icon="update"
				label={ __(
					'Refresh offers from Life Coach Hub',
					'lifecoachhub'
				) }
				onClick={ () => loadStore( true ) }
				disabled={ isLoading }
			/>
			{ isPickerOpen && (
				<LayoutPicker
					blockName={ BLOCK_NAME }
					attributes={ attributes }
					setAttributes={ setAttributes }
					onClose={ () => setIsPickerOpen( false ) }
				/>
			) }
		</BlockControls>
	);

	if ( isLoading && ! store ) {
		return (
			<div { ...blockProps }>
				{ inspector }
				<Placeholder
					icon="store"
					label={ __( 'Life Coach Hub Shop', 'lifecoachhub' ) }
				>
					<Spinner />
				</Placeholder>
			</div>
		);
	}

	if ( error ) {
		const notConnected = error?.code === 'lifecoachhub_not_connected';
		return (
			<div { ...blockProps }>
				{ inspector }
				{ toolbar }
				<Placeholder
					icon="store"
					label={ __( 'Life Coach Hub Shop', 'lifecoachhub' ) }
					instructions={
						error?.message ??
						__( 'Your shop could not be loaded.', 'lifecoachhub' )
					}
				>
					{ notConnected ? (
						<Button
							variant="primary"
							href={ error?.data?.settings_url }
						>
							{ __( 'Connect Life Coach Hub', 'lifecoachhub' ) }
						</Button>
					) : (
						<Button
							variant="secondary"
							onClick={ () => loadStore( true ) }
						>
							{ __( 'Try again', 'lifecoachhub' ) }
						</Button>
					) }
				</Placeholder>
			</div>
		);
	}

	if ( offers.length === 0 ) {
		return (
			<div { ...blockProps }>
				{ inspector }
				{ toolbar }
				<Placeholder
					icon="store"
					label={ __( 'Life Coach Hub Shop', 'lifecoachhub' ) }
					instructions={
						isManual
							? __(
									'Pick the offers to show in the block settings.',
									'lifecoachhub'
							  )
							: __(
									'No published offers to show yet. Offers shown in your Life Coach Hub public store appear here.',
									'lifecoachhub'
							  )
					}
				/>
			</div>
		);
	}

	const showToolbar =
		( showFilters && presentTypes.length > 1 ) ||
		showSearch ||
		( showSort && offers.length > 1 );

	return (
		<div { ...blockProps }>
			{ inspector }
			{ toolbar }
			{ showToolbar && (
				<div className="lifecoachhub-shop__toolbar">
					{ showFilters && presentTypes.length > 1 && (
						<div className="lifecoachhub-shop__filters">
							{ [ 'all', ...presentTypes ].map(
								( type, index ) => (
									<span
										key={ type }
										className={ `lifecoachhub-shop__filter${
											index === 0 ? ' is-active' : ''
										}` }
									>
										{ type === 'all'
											? allLabel
											: labels[ type ] }
									</span>
								)
							) }
						</div>
					) }
					{ ( showSearch || ( showSort && offers.length > 1 ) ) && (
						<div className="lifecoachhub-shop__controls">
							{ showSearch && (
								<input
									type="search"
									className="lifecoachhub-shop__search"
									placeholder={ __(
										'Search offers',
										'lifecoachhub'
									) }
									disabled
								/>
							) }
							{ showSort && offers.length > 1 && (
								<select
									className="lifecoachhub-shop__sort"
									disabled
								>
									<option>
										{ SORT_LABELS[ effectiveSort ] }
									</option>
								</select>
							) }
						</div>
					) }
				</div>
			) }
			<ul className="lifecoachhub-shop__grid">
				{ offers.map( ( offer ) => (
					<li key={ offer.id } className="lifecoachhub-shop__card">
						{ showImage && offer.image_url && (
							<div className="lifecoachhub-shop__media">
								<img src={ offer.image_url } alt="" />
							</div>
						) }
						<div className="lifecoachhub-shop__body">
							{ showTypeLabel && (
								<span className="lifecoachhub-shop__type">
									{ labels[ offer.type ] }
								</span>
							) }
							<HeadingTag className="lifecoachhub-shop__title">
								{ offer.title }
							</HeadingTag>
							{ showDescription && offer.description && (
								<p className="lifecoachhub-shop__description">
									{ offer.description }
								</p>
							) }
							<div className="lifecoachhub-shop__footer">
								{ showPrice && (
									<p className="lifecoachhub-shop__price">
										{ offer.price?.is_free ? (
											<strong>
												{ __( 'Free', 'lifecoachhub' ) }
											</strong>
										) : (
											<>
												<strong>
													{ offer.price?.formatted }
												</strong>
												{ offer.price?.cadence &&
													CADENCE_LABELS[
														offer.price.cadence
													] && (
														<span className="lifecoachhub-shop__cadence">
															{
																CADENCE_LABELS[
																	offer.price
																		.cadence
																]
															}
														</span>
													) }
												{ offer.price
													?.original_formatted && (
													<del className="lifecoachhub-shop__original">
														{
															offer.price
																.original_formatted
														}
													</del>
												) }
											</>
										) }
									</p>
								) }
								<div className="lifecoachhub-shop__actions">
									{ offer.sold_out ? (
										<span className="lifecoachhub-shop__sold-out">
											{ __( 'Sold out', 'lifecoachhub' ) }
										</span>
									) : (
										<span className="wp-element-button lifecoachhub-shop__buy">
											{ buyLabel ||
												__(
													'Buy now',
													'lifecoachhub'
												) }
										</span>
									) }
									{ offer.details_url && (
										<span
											className={
												detailsStyle === 'button'
													? 'wp-element-button lifecoachhub-shop__details lifecoachhub-shop__details--button'
													: 'lifecoachhub-shop__details'
											}
										>
											{ detailsLabel ||
												__(
													'Learn more',
													'lifecoachhub'
												) }
										</span>
									) }
								</div>
							</div>
						</div>
					</li>
				) ) }
			</ul>
		</div>
	);
}
