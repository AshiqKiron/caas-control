<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'caas_control_settings' );

global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key = 'caas_control_sidekick'" );
