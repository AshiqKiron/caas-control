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
	 * Register top-level admin menu and submenus.
	 */
	public function register_menu(): void {
		add_menu_page(
			__( 'CaaS Control', 'caas-control' ),
			'🐾 ' . __( 'CaaS Control', 'caas-control' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'render_global_menace_page' ),
			'dashicons-pets',
			79
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Global Menace', 'caas-control' ),
			__( 'Global Menace', 'caas-control' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'render_global_menace_page' )
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Litter Box & Care', 'caas-control' ),
			__( 'Litter Box & Care', 'caas-control' ),
			'manage_options',
			$this->plugin_name . '-care',
			array( $this, 'render_litter_box_page' )
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Pro & Breed Shop', 'caas-control' ),
			__( 'Pro & Breed Shop', 'caas-control' ),
			'manage_options',
			$this->plugin_name . '-pro',
			array( $this, 'render_pro_shop_page' )
		);
	}

	/**
	 * Register Settings API groups.
	 */
	public function register_settings(): void {
		register_setting(
			'caas_control_menace',
			Caas_Control_Settings::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( Caas_Control_Settings::class, 'sanitize' ),
				'default'           => Caas_Control_Settings::defaults(),
			)
		);

		register_setting(
			'caas_control_pro_dev',
			Caas_Control_Settings::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( Caas_Control_Settings::class, 'sanitize' ),
				'default'           => Caas_Control_Settings::defaults(),
			)
		);
	}

	/**
	 * Save sidekick from Litter Box page.
	 */
	public function maybe_save_sidekick(): void {
		if ( ! isset( $_POST['caas_control_sidekick_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['caas_control_sidekick_nonce'] ) ), 'caas_control_sidekick' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$allowed = array( 'orange_tabby', 'tuxedo', 'sleeping_calico' );
		$raw     = isset( $_POST['caas_control_sidekick_choice'] )
			? sanitize_text_field( wp_unslash( $_POST['caas_control_sidekick_choice'] ) )
			: 'orange_tabby';

		if ( in_array( $raw, $allowed, true ) ) {
			update_user_meta( get_current_user_id(), 'caas_control_sidekick', $raw );
		}

		add_settings_error(
			'caas_control_sidekick',
			'sidekick_saved',
			__( 'Sidekick profile updated.', 'caas-control' ),
			'updated'
		);
	}

	public function render_global_menace_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		require CAAS_CONTROL_PLUGIN_DIR . 'admin/partials/global-menace.php';
	}

	public function render_litter_box_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		settings_errors( 'caas_control_sidekick' );
		require CAAS_CONTROL_PLUGIN_DIR . 'admin/partials/litter-box-care.php';
	}

	public function render_pro_shop_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		require CAAS_CONTROL_PLUGIN_DIR . 'admin/partials/pro-breed-shop.php';
	}

	public function enqueue_styles(): void {
		if ( ! $this->is_plugin_screen() && ! $this->should_load_admin_chaos() ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			CAAS_CONTROL_PLUGIN_URL . 'admin/css/caas-control-admin.css',
			array(),
			$this->version,
			'all'
		);

		if ( $this->should_load_admin_chaos() ) {
			wp_enqueue_style(
				$this->plugin_name . '-public-chaos',
				CAAS_CONTROL_PLUGIN_URL . 'public/css/caas-control-public.css',
				array(),
				$this->version,
				'all'
			);
		}
	}

	public function enqueue_scripts(): void {
		$on_settings = $this->is_plugin_screen();

		if ( $on_settings ) {
			wp_enqueue_media();
			wp_enqueue_script(
				$this->plugin_name,
				CAAS_CONTROL_PLUGIN_URL . 'admin/js/caas-control-admin.js',
				array( 'jquery' ),
				$this->version,
				true
			);

			wp_localize_script(
				$this->plugin_name,
				'caasControlAdmin',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'caas_control_maintenance' ),
					'i18n'    => array(
						'working' => __( 'Working…', 'caas-control' ),
						'proSoon' => __( 'Pro upgrades coming soon—feed the cat in the meantime.', 'caas-control' ),
					),
				)
			);
		}

		if ( $this->should_load_admin_chaos() ) {
			$this->enqueue_chaos_script( 'backend' );
		}

		if ( $on_settings && $this->is_block_editor_screen() ) {
			$this->enqueue_block_editor_extras();
		}
	}

	/**
	 * Block editor-only productivity scripts.
	 */
	public function enqueue_block_editor_assets(): void {
		if ( ! Caas_Control_Settings::should_load_chaos( 'backend' ) ) {
			return;
		}

		$this->enqueue_chaos_script( 'block-editor' );
		wp_enqueue_script(
			$this->plugin_name . '-editor',
			CAAS_CONTROL_PLUGIN_URL . 'admin/js/caas-control-editor.js',
			array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data' ),
			$this->version,
			true
		);
	}

	private function enqueue_block_editor_extras(): void {
		// Fallback when block editor assets hook is unavailable.
	}

	private function enqueue_chaos_script( string $context ): void {
		wp_enqueue_script(
			$this->plugin_name . '-chaos',
			CAAS_CONTROL_PLUGIN_URL . 'public/js/caas-control-chaos.js',
			array(),
			$this->version,
			true
		);

		$user_id  = get_current_user_id();
		$sidekick = $user_id ? get_user_meta( $user_id, 'caas_control_sidekick', true ) : 'orange_tabby';

		wp_localize_script(
			$this->plugin_name . '-chaos',
			'caasControlChaos',
			array_merge(
				Caas_Control_Settings::chaos_config_for_js(),
				array(
					'context'  => $context,
					'sidekick' => is_string( $sidekick ) ? $sidekick : 'orange_tabby',
					'isAdmin'  => true,
				)
			)
		);
	}

	private function is_plugin_screen(): bool {
		$screen = get_current_screen();
		return $screen && strpos( $screen->id, $this->plugin_name ) !== false;
	}

	private function is_block_editor_screen(): bool {
		$screen = get_current_screen();
		return $screen && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor();
	}

	private function should_load_admin_chaos(): bool {
		if ( ! is_admin() || ! Caas_Control_Settings::should_load_chaos( 'backend' ) ) {
			return false;
		}

		$settings = Caas_Control_Settings::get_all();
		if ( ! empty( $settings['habitat_specific_only'] ) && ! $this->is_plugin_screen() && ! $this->is_block_editor_screen() ) {
			return false;
		}

		return true;
	}
}
