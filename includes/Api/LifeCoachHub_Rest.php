<?php
/**
 * REST route the block editor uses to preview the shop.
 *
 * @package LifeCoachHub\Api
 */

namespace LifeCoachHub\Api;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers lifecoachhub/v1/store and keeps the store cache in sync with the connection.
 */
class LifeCoachHub_Rest {

	const REST_NAMESPACE = 'lifecoachhub/v1';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		// A new or removed connection means a different (or no) shop.
		add_action( 'update_option_lifecoachhub_api_key', array( LifeCoachHub_Store::class, 'clear' ) );
		add_action( 'add_option_lifecoachhub_api_key', array( LifeCoachHub_Store::class, 'clear' ) );
		add_action( 'delete_option_lifecoachhub_api_key', array( LifeCoachHub_Store::class, 'clear' ) );
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/store',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_store' ),
				'permission_callback' => array( $this, 'can_read_store' ),
				'args'                => array(
					'refresh' => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			)
		);
	}

	/**
	 * Only people who can edit content need the editor preview; refreshing is for admins.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool
	 */
	public function can_read_store( WP_REST_Request $request ) {
		if ( $request->get_param( 'refresh' ) ) {
			return current_user_can( 'manage_options' );
		}

		return current_user_can( 'edit_posts' );
	}

	/**
	 * Return the cached store.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_store( WP_REST_Request $request ) {
		$store = LifeCoachHub_Store::get( (bool) $request->get_param( 'refresh' ) );

		if ( is_wp_error( $store ) ) {
			$status = 'lifecoachhub_not_connected' === $store->get_error_code() ? 409 : 502;
			$store->add_data(
				array(
					'status'       => $status,
					'settings_url' => admin_url( 'admin.php?page=lifecoachhub-settings' ),
				)
			);

			return $store;
		}

		return rest_ensure_response(
			array(
				'store'        => $store,
				'settings_url' => admin_url( 'admin.php?page=lifecoachhub-settings' ),
			)
		);
	}
}
