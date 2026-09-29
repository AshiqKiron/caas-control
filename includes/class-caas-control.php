<?php
/**
 * Core plugin class.
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Orchestrates hooks and loads dependencies.
 */
class Caas_Control {

	/**
	 * Hook loader.
	 *
	 * @var Caas_Control_Loader
	 */
	protected Caas_Control_Loader $loader;

	/**
	 * Plugin identifier.
	 *
	 * @var string
	 */
	protected string $plugin_name = 'caas-control';

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected string $version;

	public function __construct() {
		$this->version = defined( 'CAAS_CONTROL_VERSION' ) ? CAAS_CONTROL_VERSION : '1.0.0';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load required files.
	 */
	private function load_dependencies(): void {
		require_once CAAS_CONTROL_PLUGIN_DIR . 'admin/class-caas-control-admin.php';
		require_once CAAS_CONTROL_PLUGIN_DIR . 'public/class-caas-control-public.php';

		$this->loader = new Caas_Control_Loader();
	}

	/**
	 * Register text domain for translations.
	 */
	private function set_locale(): void {
		$this->loader->add_action(
			'plugins_loaded',
			$this,
			'load_plugin_textdomain'
		);
	}

	/**
	 * Load plugin translations.
	 */
	public function load_plugin_textdomain(): void {
		load_plugin_textdomain(
			'caas-control',
			false,
			dirname( CAAS_CONTROL_PLUGIN_BASENAME ) . '/languages/'
		);
	}

	/**
	 * Admin-facing hooks.
	 */
	private function define_admin_hooks(): void {
		$admin = new Caas_Control_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $admin, 'register_menu' );
	}

	/**
	 * Public-facing hooks.
	 */
	private function define_public_hooks(): void {
		$public = new Caas_Control_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader.
	 */
	public function run(): void {
		$this->loader->run();
	}

	public function get_plugin_name(): string {
		return $this->plugin_name;
	}

	public function get_loader(): Caas_Control_Loader {
		return $this->loader;
	}

	public function get_version(): string {
		return $this->version;
	}
}
