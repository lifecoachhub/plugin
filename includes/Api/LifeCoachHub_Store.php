<?php
/**
 * Fetches the connected coach's public store from Life Coach Hub.
 *
 * @package LifeCoachHub\Api
 */

namespace LifeCoachHub\Api;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Server-side client for the Life Coach Hub store endpoint.
 *
 * The API key never leaves the server: blocks render from this cached copy,
 * and the editor reads it through the plugin's own REST route.
 */
class LifeCoachHub_Store {

	const CACHE_KEY      = 'lifecoachhub_store';
	const LAST_GOOD_KEY  = 'lifecoachhub_store_last_good';
	const CACHE_TTL      = 10 * MINUTE_IN_SECONDS;
	const ENDPOINT       = '/api/v1/wordpress/store';
	const REQUEST_TIMEOUT = 8;

	/**
	 * Get the store data, from cache when possible.
	 *
	 * @param bool $force_refresh Skip the cache and fetch from Life Coach Hub.
	 * @return array{coach: array, currency: array, offers: array<int, array>}|WP_Error
	 */
	public static function get( $force_refresh = false ) {
		$api_key = (string) get_option( 'lifecoachhub_api_key', '' );
		if ( '' === $api_key ) {
			return new WP_Error( 'lifecoachhub_not_connected', __( 'Connect Life Coach Hub to show your shop.', 'lifecoachhub' ) );
		}

		if ( ! $force_refresh ) {
			$cached = get_transient( self::CACHE_KEY );
			if ( is_array( $cached ) ) {
				return $cached;
			}
		}

		$store = self::fetch( $api_key );

		if ( is_wp_error( $store ) ) {
			// Keep the shop up while Life Coach Hub can't be reached.
			$last_good = get_option( self::LAST_GOOD_KEY );
			if ( is_array( $last_good ) ) {
				set_transient( self::CACHE_KEY, $last_good, MINUTE_IN_SECONDS );
				return $last_good;
			}

			return $store;
		}

		set_transient( self::CACHE_KEY, $store, self::CACHE_TTL );
		update_option( self::LAST_GOOD_KEY, $store, false );

		return $store;
	}

	/**
	 * Forget the cached store, e.g. after connecting or disconnecting.
	 */
	public static function clear() {
		delete_transient( self::CACHE_KEY );
		delete_option( self::LAST_GOOD_KEY );
	}

	/**
	 * Request the store from Life Coach Hub.
	 *
	 * @param string $api_key Connection API key.
	 * @return array|WP_Error
	 */
	private static function fetch( $api_key ) {
		$response = wp_remote_get(
			lifecoachhub_get_app_url( self::ENDPOINT ),
			array(
				'timeout' => self::REQUEST_TIMEOUT,
				'headers' => array(
					'Accept'    => 'application/json',
					'X-LCH-Key' => $api_key,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 401 === $code ) {
			return new WP_Error( 'lifecoachhub_invalid_key', __( 'Your Life Coach Hub connection has expired. Please reconnect.', 'lifecoachhub' ) );
		}

		if ( 200 !== $code || ! isset( $body['data']['offers'] ) || ! is_array( $body['data']['offers'] ) ) {
			return new WP_Error( 'lifecoachhub_store_unavailable', __( 'Your Life Coach Hub shop could not be loaded right now.', 'lifecoachhub' ) );
		}

		return array(
			'coach'    => isset( $body['data']['coach'] ) && is_array( $body['data']['coach'] ) ? $body['data']['coach'] : array(),
			'currency' => isset( $body['data']['currency'] ) && is_array( $body['data']['currency'] ) ? $body['data']['currency'] : array(),
			'offers'   => array_values( array_filter( $body['data']['offers'], 'is_array' ) ),
		);
	}
}
