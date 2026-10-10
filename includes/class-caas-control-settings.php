<?php
/**
 * Plugin settings defaults and helpers.
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Central settings for CaaS chaos configuration.
 */
class Caas_Control_Settings {

	public const OPTION_KEY = 'caas_control_settings';

	/**
	 * Default option values.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		return array(
			'version'                 => CAAS_CONTROL_VERSION,
			'caas_enabled'            => true,
			'agent'                   => 'cat',
			'companion_name'          => 'Sir Paws-a-Lot',
			'frequency_minutes'       => 15,
			'duration_seconds'        => 5,
			'habitat_frontend'        => true,
			'habitat_backend'         => true,
			'habitat_specific_only'   => false,
			'behavior_walk'           => true,
			'behavior_sit'            => true,
			'behavior_follow'         => true,
			'behavior_block_mouse'    => true,
			'behavior_meow'           => false,
			'behavior_random_block'   => true,
			'custom_image_url'        => '',
			'enable_404_guilt'        => true,
			'enable_publish_gate'     => true,
			'enable_idle_judgment'    => true,
			'enable_login_hiss'       => true,
			'enable_keyboard_trample' => true,
			'enable_red_dot'          => false,
			'enable_fake_security'    => true,
			'enable_footer_peekaboo'  => true,
			'enable_paw_trail'        => false,
			'enable_scroll_post'      => true,
			'enable_woo_cart_wiggle'  => true,
			'pro_enabled'             => false,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_all(): array {
		$stored = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$merged = array_merge( self::defaults(), $stored );
		$agents = array( 'cat', 'custom' );
		if ( ! in_array( $merged['agent'], $agents, true ) ) {
			$merged['agent'] = 'cat';
		}

		$allowed_freq = array( 5, 10, 15 );
		if ( ! in_array( (int) $merged['frequency_minutes'], $allowed_freq, true ) ) {
			$merged['frequency_minutes'] = self::defaults()['frequency_minutes'];
		}

		return $merged;
	}

	/**
	 * @param mixed $value Raw input.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $value ): array {
		if ( ! is_array( $value ) ) {
			return self::defaults();
		}

		$form_context = isset( $value['_form_context'] ) ? sanitize_key( $value['_form_context'] ) : 'menace';
		unset( $value['_form_context'] );

		$agents = array( 'cat', 'custom' );
		$freq   = array( 5, 10, 15 );
		$dur    = array( 5, 10, 30 );

		$out = self::get_all();

		if ( isset( $value['agent'] ) && in_array( $value['agent'], $agents, true ) ) {
			$out['agent'] = $value['agent'];
		}

		$out['companion_name'] = isset( $value['companion_name'] )
			? sanitize_text_field( wp_unslash( $value['companion_name'] ) )
			: $out['companion_name'];

		if ( isset( $value['frequency_minutes'] ) && in_array( (int) $value['frequency_minutes'], $freq, true ) ) {
			$out['frequency_minutes'] = (int) $value['frequency_minutes'];
		}

		if ( isset( $value['duration_seconds'] ) && in_array( (int) $value['duration_seconds'], $dur, true ) ) {
			$out['duration_seconds'] = (int) $value['duration_seconds'];
		}

		$bool_keys = array(
			'caas_enabled',
			'habitat_frontend',
			'habitat_backend',
			'habitat_specific_only',
			'behavior_walk',
			'behavior_sit',
			'behavior_follow',
			'behavior_block_mouse',
			'behavior_meow',
			'behavior_random_block',
			'enable_404_guilt',
			'enable_publish_gate',
			'enable_idle_judgment',
			'enable_login_hiss',
			'enable_keyboard_trample',
			'enable_red_dot',
			'enable_fake_security',
			'enable_footer_peekaboo',
			'enable_paw_trail',
			'enable_scroll_post',
			'enable_woo_cart_wiggle',
			'pro_enabled',
		);

		if ( 'pro' === $form_context ) {
			$out['pro_enabled'] = ! empty( $value['pro_enabled'] );
		} else {
			foreach ( $bool_keys as $key ) {
				if ( 'pro_enabled' === $key ) {
					continue;
				}
				$out[ $key ] = ! empty( $value[ $key ] );
			}
		}

		$out['custom_image_url'] = isset( $value['custom_image_url'] )
			? esc_url_raw( wp_unslash( $value['custom_image_url'] ) )
			: '';

		return $out;
	}

	/**
	 * Whether CaaS chaos and features are active site-wide.
	 */
	public static function is_enabled(): bool {
		return ! empty( self::get_all()['caas_enabled'] );
	}

	/**
	 * Whether chaos assets should load in the current context.
	 */
	public static function should_load_chaos( string $context ): bool {
		if ( ! self::is_enabled() ) {
			return false;
		}

		$s = self::get_all();

		if ( $context === 'backend' ) {
			return (bool) $s['habitat_backend'] && is_admin();
		}

		if ( $context === 'frontend' ) {
			return (bool) $s['habitat_frontend'] && ! is_admin();
		}

		return false;
	}

	/**
	 * Public URL for a file under the plugin assets/ directory.
	 */
	public static function asset_url( string $relative_path ): string {
		return CAAS_CONTROL_PLUGIN_URL . 'assets/' . ltrim( $relative_path, '/' );
	}

	/**
	 * First readable bundled file under assets/gpl-cat/.
	 *
	 * @param list<string> $filenames Basename order (first match wins).
	 */
	public static function bundled_gpl_cat_url( array $filenames ): string {
		$base = CAAS_CONTROL_PLUGIN_DIR . 'assets/gpl-cat/';
		foreach ( $filenames as $file ) {
			if ( is_readable( $base . $file ) ) {
				return self::asset_url( 'gpl-cat/' . $file );
			}
		}

		return '';
	}

	/**
	 * Default Pawtrol officer sprite (prefers MP4/WebM, then GIF, then PNG).
	 */
	public static function default_cat_media_url(): string {
		$url = self::bundled_gpl_cat_url(
			array( 'gpl-cat.mp4', 'gpl-cat.webm', 'gpl-cat.gif', 'gpl-cat.png' )
		);
		if ( $url !== '' ) {
			return $url;
		}

		return self::asset_url( 'gpl-cat/gpl-cat.png' );
	}

	/**
	 * Whether a media URL should render as HTML video (not GIF/static image).
	 */
	public static function agent_media_is_video( string $url ): bool {
		return (bool) preg_match( '/\.(mp4|webm|ogg)(\?|#|$)/i', $url );
	}

	/**
	 * Bundled sit-and-judge clip (prefers MP4/WebM when present).
	 */
	public static function judging_video_url(): string {
		return self::bundled_gpl_cat_url( array( 'judging.mp4', 'judging.webm', 'judging.gif' ) );
	}

	/**
	 * Config blob for JavaScript.
	 *
	 * @return array<string, mixed>
	 */
	public static function chaos_config_for_js(): array {
		$s = self::get_all();

		return array(
			'agent'              => $s['agent'],
			'companionName'      => $s['companion_name'],
			'frequencyMinutes'   => (int) $s['frequency_minutes'],
			'durationSeconds'    => (int) $s['duration_seconds'],
			'customImageUrl'     => $s['custom_image_url'],
			'catImageUrl'        => self::default_cat_media_url(),
			'meowSoundUrl'       => self::asset_url( 'gpl-cat/meow.ogg' ),
			'judgingVideoUrl'    => self::judging_video_url(),
			'behaviors'          => array(
				'walk'         => (bool) $s['behavior_walk'],
				'sit'          => (bool) $s['behavior_sit'],
				'follow'       => (bool) $s['behavior_follow'],
				'blockMouse'   => (bool) $s['behavior_block_mouse'],
				'meow'         => (bool) $s['behavior_meow'],
				'randomBlock'  => (bool) $s['behavior_random_block'],
			),
			'features'           => array(
				'publishGate'       => (bool) $s['enable_publish_gate'],
				'idleJudgment'      => (bool) $s['enable_idle_judgment'],
				'keyboardTrample'   => (bool) $s['enable_keyboard_trample'],
				'redDot'            => (bool) $s['enable_red_dot'],
				'fakeSecurity'      => (bool) $s['enable_fake_security'],
				'footerPeekaboo'    => (bool) $s['enable_footer_peekaboo'],
				'pawTrail'          => (bool) $s['enable_paw_trail'],
				'scrollPost'        => (bool) $s['enable_scroll_post'],
				'wooCartWiggle'     => (bool) $s['enable_woo_cart_wiggle'],
			),
			'proEnabled'         => (bool) $s['pro_enabled'],
			'strings'            => array(
				'publishGate'    => __( 'Cat is reviewing your changes.', 'caas-control' ),
				'idleJudgment'   => __( 'You\'ve been a sitting duck on this page for 15 minutes. Move on, fella.', 'caas-control' ),
				'fakeSecurity'   => __( 'Suspicious mouse movement detected!', 'caas-control' ),
				'seoRejection'   => __( 'Fill out your SEO meta description before I let you publish.', 'caas-control' ),
				'scrollPost'     => __( 'Scramble!', 'caas-control' ),
				'judgingYou'     => sprintf(
					/* translators: %s: companion display name */
					__( '%s is judging you.', 'caas-control' ),
					$s['companion_name']
				),
			),
			'i18n'               => array(
				'meow' => __( 'Meow!', 'caas-control' ),
			),
		);
	}
}
