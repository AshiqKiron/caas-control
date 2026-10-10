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

		require_once CAAS_CONTROL_PLUGIN_DIR . 'includes/class-caas-control-settings.php';

		if ( false === get_option( Caas_Control_Settings::OPTION_KEY ) ) {
			add_option( Caas_Control_Settings::OPTION_KEY, Caas_Control_Settings::defaults(), '', false );
		}

		flush_rewrite_rules();
	}
}
