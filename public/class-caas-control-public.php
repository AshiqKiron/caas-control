<?php
/**
 * Public-facing functionality.
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines public-specific hooks.
 */
class Caas_Control_Public {

	private string $plugin_name;
	private string $version;

	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function enqueue_styles(): void {
		wp_enqueue_style(
			$this->plugin_name,
			CAAS_CONTROL_PLUGIN_URL . 'public/css/caas-control-public.css',
			array(),
			$this->version,
			'all'
		);
	}

	public function enqueue_scripts(): void {
		wp_enqueue_script(
			$this->plugin_name,
			CAAS_CONTROL_PLUGIN_URL . 'public/js/caas-control-public.js',
			array(),
			$this->version,
			true
		);
	}
}
