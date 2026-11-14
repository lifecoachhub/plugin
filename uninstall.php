<?php
/**
 * Removes the plugin's stored connection data when the plugin is deleted.
 *
 * @package LifeCoachHub
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'lifecoachhub_api_key' );
delete_option( 'lifecoachhub_connection_status' );
delete_option( 'lifecoachhub_connected_at' );
