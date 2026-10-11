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
	 * Info icon with accessible tooltip for settings labels.
	 *
	 * @param string $text Help copy (plain text).
	 */
	public static function field_tip( string $text ): string {
		$text = trim( $text );
		if ( '' === $text ) {
			return '';
		}

		$plain = wp_strip_all_tags( $text );
		$label = sprintf(
			/* translators: %s: help text */
			__( 'More information: %s', 'caas-control' ),
			$plain
		);

		return sprintf(
			'<span class="caas-control-tip"><button type="button" class="caas-control-tip__trigger" aria-label="%1$s"><span class="dashicons dashicons-info-outline" aria-hidden="true"></span></button><span class="caas-control-tip__bubble" role="tooltip">%2$s</span></span>',
			esc_attr( $label ),
			esc_html( $text )
		);
	}

	/**
	 * Register top-level admin menu and submenus.
	 */
	public function register_menu(): void {
		add_menu_page(
			__( 'CaaS Control', 'caas-control' ),
			__( 'CaaS Control', 'caas-control' ),
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

	public function enqueue_styles( string $hook_suffix = '' ): void {
		if ( ! $this->is_caas_admin_page( $hook_suffix ) && ! $this->should_load_admin_chaos( $hook_suffix ) && ! $this->is_user_profile_screen() ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			CAAS_CONTROL_PLUGIN_URL . 'admin/css/caas-control-admin.css',
			array( 'dashicons' ),
			$this->version,
			'all'
		);

		if ( $this->should_load_public_chaos_styles( $hook_suffix ) ) {
			wp_enqueue_style(
				$this->plugin_name . '-public-chaos',
				CAAS_CONTROL_PLUGIN_URL . 'public/css/caas-control-public.css',
				array(),
				$this->version,
				'all'
			);
		}
	}

	public function enqueue_scripts( string $hook_suffix = '' ): void {
		$on_settings = $this->is_caas_admin_page( $hook_suffix );
		$on_profile  = $this->is_user_profile_screen();

		$load_chaos   = $this->should_load_admin_chaos( $hook_suffix );
		$chaos_loaded = false;

		// Settings screens always use the preview harness (manual Preview buttons), not live chaos hooks.
		if ( $on_settings ) {
			$this->enqueue_chaos_script( 'backend', true );
			$chaos_loaded = true;
		} elseif ( $load_chaos ) {
			$this->enqueue_chaos_script( 'backend', false );
			$chaos_loaded = true;
		}

		if ( $on_settings || $on_profile ) {
			$admin_deps = array( 'jquery' );
			if ( $chaos_loaded && $on_settings ) {
				$admin_deps[] = $this->plugin_name . '-chaos';
			}

			wp_enqueue_script(
				$this->plugin_name,
				CAAS_CONTROL_PLUGIN_URL . 'admin/js/caas-control-admin.js',
				$admin_deps,
				$this->version,
				true
			);
		}

		if ( $on_settings ) {
			wp_enqueue_media();
			wp_localize_script(
				$this->plugin_name,
				'caasControlAdmin',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'caas_control_maintenance' ),
					'i18n'    => array(
						'working'             => __( 'Working…', 'caas-control' ),
						'proSoon'             => __( 'Pro upgrades coming soon—feed the cat in the meantime.', 'caas-control' ),
						'mediaMissing'        => __( 'Media library is not available on this screen.', 'caas-control' ),
						'previewUnavailable'  => __( 'Preview could not run. Reload the page and try again.', 'caas-control' ),
						'durationMinsOnly'    => __( '%s mins', 'caas-control' ),
						'durationMinsSeconds' => __( '%1$s mins %2$s seconds', 'caas-control' ),
						'durationSeconds'     => __( 'seconds', 'caas-control' ),
						'durationSecondsMax'  => Caas_Control_Settings::duration_seconds_max(),
						'durationUntilYourCry' => __( 'Until your cry - 10 mins', 'caas-control' ),
						'appearAfterHoursMins' => __( '%1$s hour %2$s mins', 'caas-control' ),
						'appearAfterHourLabelAboveMinutes' => 55,
					),
				)
			);
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

	private function enqueue_chaos_script( string $context, bool $preview_harness = false ): void {
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
					'context'         => $context,
					'sidekick'        => is_string( $sidekick ) ? $sidekick : 'orange_tabby',
					'isAdmin'         => true,
					'previewHarness'  => $preview_harness,
				)
			)
		);
	}

	/**
	 * Preview button for a timed companion behavior (Global Menace).
	 */
	public static function behavior_preview_button( string $behavior_slug ): string {
		return sprintf(
			'<button type="button" class="button button-small caas-control-behavior-preview" data-caas-behavior="%1$s">%2$s</button>',
			esc_attr( $behavior_slug ),
			esc_html__( 'Preview', 'caas-control' )
		);
	}

	/**
	 * Preview button for an event-driven chaos feature (Global Menace).
	 */
	public static function feature_preview_button( string $feature_slug ): string {
		return sprintf(
			'<button type="button" class="button button-small caas-control-feature-preview" data-caas-feature="%1$s">%2$s</button>',
			esc_attr( $feature_slug ),
			esc_html__( 'Preview', 'caas-control' )
		);
	}

	private function should_load_public_chaos_styles( string $hook_suffix = '' ): bool {
		if ( $this->should_load_admin_chaos( $hook_suffix ) ) {
			return true;
		}

		return $this->is_caas_admin_page( $hook_suffix );
	}

	/**
	 * Whether the current admin request is a CaaS plugin settings page.
	 *
	 * @param string $hook_suffix Value passed to admin_enqueue_scripts (preferred).
	 */
	private function is_caas_admin_page( string $hook_suffix = '' ): bool {
		if ( $hook_suffix !== '' && strpos( $hook_suffix, $this->plugin_name ) !== false ) {
			return true;
		}

		$screen = get_current_screen();
		if ( $screen && strpos( $screen->id, $this->plugin_name ) !== false ) {
			return true;
		}

		if ( isset( $_GET['page'] ) ) {
			$page = sanitize_key( wp_unslash( (string) $_GET['page'] ) );
			return strpos( $page, $this->plugin_name ) === 0;
		}

		return false;
	}

	private function is_block_editor_screen(): bool {
		$screen = get_current_screen();
		return $screen && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor();
	}

	/**
	 * User profile screens (sidekick field tooltips).
	 */
	private function is_user_profile_screen(): bool {
		if ( ! Caas_Control_Settings::is_enabled() ) {
			return false;
		}

		$screen = get_current_screen();
		return $screen && in_array( $screen->base, array( 'profile', 'user-edit' ), true );
	}

	private function should_load_admin_chaos( string $hook_suffix = '' ): bool {
		if ( ! is_admin() || ! Caas_Control_Settings::should_load_chaos( 'backend' ) ) {
			return false;
		}

		$settings = Caas_Control_Settings::get_all();
		if ( ! empty( $settings['habitat_specific_only'] ) && ! $this->is_caas_admin_page( $hook_suffix ) && ! $this->is_block_editor_screen() ) {
			return false;
		}

		return true;
	}
}
