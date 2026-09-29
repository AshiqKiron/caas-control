<?php
/**
 * Fired during plugin deactivation.
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivation logic.
 */
class Caas_Control_Deactivator {

	/**
	 * Run deactivation tasks.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
