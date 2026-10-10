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
		if ( ! Caas_Control_Settings::should_load_chaos( 'frontend' ) ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			CAAS_CONTROL_PLUGIN_URL . 'public/css/caas-control-public.css',
			array(),
			$this->version,
			'all'
		);
	}

	public function enqueue_scripts(): void {
		if ( ! Caas_Control_Settings::should_load_chaos( 'frontend' ) ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name . '-chaos',
			CAAS_CONTROL_PLUGIN_URL . 'public/js/caas-control-chaos.js',
			array(),
			$this->version,
			true
		);

		wp_localize_script(
			$this->plugin_name . '-chaos',
			'caasControlChaos',
			array_merge(
				Caas_Control_Settings::chaos_config_for_js(),
				array(
					'context' => 'frontend',
					'isAdmin' => false,
				)
			)
		);
	}

	/**
	 * Scroll-to-top scratching post markup.
	 */
	public function render_scroll_post_button(): void {
		if ( ! Caas_Control_Settings::is_enabled() ) {
			return;
		}

		$settings = Caas_Control_Settings::get_all();
		if ( empty( $settings['enable_scroll_post'] ) ) {
			return;
		}

		$agent = isset( $settings['agent'] ) ? (string) $settings['agent'] : 'cat';

		echo '<div class="caas-control-scroll-post-wrap">';
		echo '<button type="button" class="caas-control-scroll-post" aria-label="' . esc_attr__( 'Back to top', 'caas-control' ) . '">';
		echo '<span class="caas-control-scroll-post-cat" aria-hidden="true">';

		if ( 'custom' === $agent && ! empty( $settings['custom_image_url'] ) ) {
			self::echo_agent_sprite( (string) $settings['custom_image_url'], 52 );
		} elseif ( 'cat' === $agent ) {
			self::echo_agent_sprite( Caas_Control_Settings::default_cat_media_url(), 52 );
		} else {
			echo '🐈';
		}

		echo '</span>';
		echo '<span class="caas-control-scroll-post-sisal" aria-hidden="true"></span>';
		echo '<span class="caas-control-scroll-post-icon" aria-hidden="true">🐾</span>';
		echo '</button>';
		echo '</div>';
	}

	/**
	 * Inline sprite for scroll-post markup (image, GIF, or muted looping video).
	 */
	private static function echo_agent_sprite( string $url, int $size ): void {
		if ( Caas_Control_Settings::agent_media_is_video( $url ) ) {
			printf(
				'<video src="%1$s" width="%2$d" height="%2$d" muted playsinline autoplay loop aria-hidden="true"></video>',
				esc_url( $url ),
				$size
			);
			return;
		}

		printf(
			'<img src="%1$s" alt="" width="%2$d" height="%2$d" />',
			esc_url( $url ),
			$size
		);
	}
}
