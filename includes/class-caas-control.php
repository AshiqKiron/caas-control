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
		$this->define_feature_hooks();
	}

	/**
	 * Load required files.
	 */
	private function load_dependencies(): void {
		require_once CAAS_CONTROL_PLUGIN_DIR . 'includes/class-caas-control-settings.php';
		require_once CAAS_CONTROL_PLUGIN_DIR . 'includes/class-caas-control-maintenance.php';
		require_once CAAS_CONTROL_PLUGIN_DIR . 'includes/class-caas-control-features.php';
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
		$admin       = new Caas_Control_Admin( $this->get_plugin_name(), $this->get_version() );
		$maintenance = new Caas_Control_Maintenance();

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'enqueue_block_editor_assets', $admin, 'enqueue_block_editor_assets' );
		$this->loader->add_action( 'admin_menu', $admin, 'register_menu' );
		$this->loader->add_action( 'admin_init', $admin, 'register_settings' );
		$this->loader->add_action( 'admin_init', $admin, 'maybe_save_sidekick' );

		$this->loader->add_action( 'wp_ajax_caas_control_scoop_revisions', $maintenance, 'ajax_scoop_revisions' );
		$this->loader->add_action( 'wp_ajax_caas_control_clear_cache', $maintenance, 'ajax_clear_cache' );
		$this->loader->add_action( 'admin_notices', $maintenance, 'site_health_vet_notice' );
		$this->loader->add_action( 'admin_notices', $maintenance, 'core_update_nudge' );
	}

	/**
	 * Public-facing hooks.
	 */
	private function define_public_hooks(): void {
		$public = new Caas_Control_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_footer', $public, 'render_scroll_post_button' );
	}

	/**
	 * Cross-cutting chaos features.
	 */
	private function define_feature_hooks(): void {
		$features = new Caas_Control_Features();

		$this->loader->add_action( 'template_redirect', $features, 'render_404_guilt' );
		$this->loader->add_action( 'login_enqueue_scripts', $features, 'enqueue_login_assets' );
		$this->loader->add_action( 'init', $features, 'register_wsod_handler' );
		$this->loader->add_action( 'show_user_profile', $features, 'render_profile_sidekick' );
		$this->loader->add_action( 'edit_user_profile', $features, 'render_profile_sidekick' );
		$this->loader->add_action( 'personal_options_update', $features, 'save_profile_sidekick' );
		$this->loader->add_action( 'edit_user_profile_update', $features, 'save_profile_sidekick' );
		$this->loader->add_action( 'init', $features, 'woo_order_celebration' );
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
