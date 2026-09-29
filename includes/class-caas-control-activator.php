<?php
/**
 * Fired during plugin activation.
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activation logic.
 */
class Caas_Control_Activator {

	/**
	 * Run activation tasks.
	 */
	public static function activate(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$defaults = array(
			'version' => CAAS_CONTROL_VERSION,
		);

		if ( false === get_option( 'caas_control_settings' ) ) {
			add_option( 'caas_control_settings', $defaults, '', false );
		}

		flush_rewrite_rules();
	}
}
