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
