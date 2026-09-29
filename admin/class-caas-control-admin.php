<?php
/**
 * Admin area functionality.
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines admin-specific hooks.
 */
class Caas_Control_Admin {

	private string $plugin_name;
	private string $version;

	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register top-level admin menu.
	 */
	public function register_menu(): void {
		add_menu_page(
			__( 'CaaS Control', 'caas-control' ),
			__( 'CaaS Control', 'caas-control' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'render_settings_page' ),
			'dashicons-admin-generic',
			80
		);
	}

	/**
	 * Settings page placeholder.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		require CAAS_CONTROL_PLUGIN_DIR . 'admin/partials/caas-control-admin-display.php';
	}

	public function enqueue_styles(): void {
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, $this->plugin_name ) === false ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			CAAS_CONTROL_PLUGIN_URL . 'admin/css/caas-control-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	public function enqueue_scripts(): void {
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, $this->plugin_name ) === false ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			CAAS_CONTROL_PLUGIN_URL . 'admin/js/caas-control-admin.js',
			array(),
			$this->version,
			true
		);
	}
}
