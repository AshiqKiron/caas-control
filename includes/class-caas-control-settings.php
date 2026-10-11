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

	private const DURATION_SECONDS_MIN  = 5;
	private const DURATION_SECONDS_MAX  = 600;
	private const DURATION_SECONDS_STEP = 5;

	private const APPEAR_AFTER_SECONDS_MIN  = 300;
	private const APPEAR_AFTER_SECONDS_MAX  = 18000;
	private const APPEAR_AFTER_SECONDS_STEP = 300;

	/** Above this many whole minutes, appear-after labels use "X hour Y mins". */
	private const APPEAR_AFTER_HOUR_LABEL_ABOVE_MINUTES = 55;

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
			'appear_after_seconds'    => 900,
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
	 * Clamp and snap chaos duration to the allowed slider range.
	 */
	public static function normalize_duration_seconds( int $seconds ): int {
		$min  = self::DURATION_SECONDS_MIN;
		$max  = self::DURATION_SECONDS_MAX;
		$step = self::DURATION_SECONDS_STEP;

		$seconds = max( $min, min( $max, $seconds ) );
		$snapped = (int) ( round( $seconds / $step ) * $step );

		return max( $min, min( $max, $snapped ) );
	}

	public static function duration_seconds_min(): int {
		return self::DURATION_SECONDS_MIN;
	}

	public static function duration_seconds_max(): int {
		return self::DURATION_SECONDS_MAX;
	}

	public static function duration_seconds_step(): int {
		return self::DURATION_SECONDS_STEP;
	}

	/**
	 * Clamp and snap initial companion delay to the allowed slider range.
	 */
	public static function normalize_appear_after_seconds( int $seconds ): int {
		$min  = self::APPEAR_AFTER_SECONDS_MIN;
		$max  = self::APPEAR_AFTER_SECONDS_MAX;
		$step = self::APPEAR_AFTER_SECONDS_STEP;

		$seconds = max( $min, min( $max, $seconds ) );
		$snapped = (int) ( round( $seconds / $step ) * $step );

		return max( $min, min( $max, $snapped ) );
	}

	public static function appear_after_seconds_min(): int {
		return self::APPEAR_AFTER_SECONDS_MIN;
	}

	public static function appear_after_seconds_max(): int {
		return self::APPEAR_AFTER_SECONDS_MAX;
	}

	public static function appear_after_seconds_step(): int {
		return self::APPEAR_AFTER_SECONDS_STEP;
	}

	/**
	 * Admin appear-after slider label (5-minute steps from 5 minutes through 5 hours).
	 *
	 * @return array{display: string, use_seconds_suffix: bool}
	 */
	public static function appear_after_slider_display( int $seconds ): array {
		$seconds = self::normalize_appear_after_seconds( $seconds );

		if ( $seconds < 60 ) {
			return array(
				'display'            => (string) $seconds,
				'use_seconds_suffix' => true,
			);
		}

		$mins = (int) floor( $seconds / 60 );

		if ( $mins > self::APPEAR_AFTER_HOUR_LABEL_ABOVE_MINUTES ) {
			$hours    = intdiv( $mins, 60 );
			$rem_mins = $mins % 60;

			return array(
				'display'            => sprintf(
					/* translators: 1: whole hours, 2: remaining minutes */
					__( '%1$s hour %2$s mins', 'caas-control' ),
					(string) $hours,
					(string) $rem_mins
				),
				'use_seconds_suffix' => false,
			);
		}

		return array(
			'display'            => sprintf(
				/* translators: %s: whole minutes */
				__( '%s mins', 'caas-control' ),
				(string) $mins
			),
			'use_seconds_suffix' => false,
		);
	}

	/**
	 * Admin duration slider label (seconds-only below 60; minutes + seconds from 60 up).
	 *
	 * @return array{display: string, use_seconds_suffix: bool}
	 */
	public static function duration_slider_display( int $seconds ): array {
		$seconds = self::normalize_duration_seconds( $seconds );

		if ( $seconds >= self::DURATION_SECONDS_MAX ) {
			return array(
				'display'            => __( 'Until your cry - 10 mins', 'caas-control' ),
				'use_seconds_suffix' => false,
			);
		}

		if ( $seconds < 60 ) {
			return array(
				'display'              => (string) $seconds,
				'use_seconds_suffix'   => true,
			);
		}

		$mins = (int) floor( $seconds / 60 );
		$secs = $seconds % 60;

		if ( 0 === $secs ) {
			return array(
				'display'              => sprintf(
					/* translators: %s: whole minutes */
					__( '%s mins', 'caas-control' ),
					(string) $mins
				),
				'use_seconds_suffix'   => false,
			);
		}

		return array(
			'display'              => sprintf(
				/* translators: 1: minutes, 2: remaining seconds */
				__( '%1$s mins %2$s seconds', 'caas-control' ),
				(string) $mins,
				(string) $secs
			),
			'use_seconds_suffix'   => false,
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

		$merged['duration_seconds'] = self::normalize_duration_seconds( (int) $merged['duration_seconds'] );

		if ( ! array_key_exists( 'appear_after_seconds', $stored ) ) {
			$merged['appear_after_seconds'] = (int) $merged['frequency_minutes'] * 60;
		} else {
			$merged['appear_after_seconds'] = self::normalize_appear_after_seconds( (int) $merged['appear_after_seconds'] );
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
		$freq = array( 5, 10, 15 );

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

		if ( isset( $value['duration_seconds'] ) ) {
			$out['duration_seconds'] = self::normalize_duration_seconds( (int) $value['duration_seconds'] );
		}

		if ( isset( $value['appear_after_seconds'] ) ) {
			$out['appear_after_seconds'] = self::normalize_appear_after_seconds( (int) $value['appear_after_seconds'] );
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
		$url = plugins_url( 'assets/' . ltrim( $relative_path, '/' ), CAAS_CONTROL_PLUGIN_FILE );

		return $url ? esc_url_raw( $url ) : '';
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
		return $url;
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
		$url = self::bundled_gpl_cat_url( array( 'judging.mp4', 'judging.webm', 'judging.gif' ) );
		if ( $url !== '' ) {
			return $url;
		}

		return self::default_cat_media_url();
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
			'appearAfterSeconds' => (int) $s['appear_after_seconds'],
			'durationSeconds'    => (int) $s['duration_seconds'],
			'customImageUrl'     => esc_url_raw( (string) $s['custom_image_url'] ),
			'catImageUrl'        => esc_url_raw( self::default_cat_media_url() ),
			'meowSoundUrl'       => esc_url_raw( self::asset_url( 'gpl-cat/meow.ogg' ) ),
			'judgingVideoUrl'    => esc_url_raw( self::judging_video_url() ),
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
				'publishGate'       => __( 'Cat is reviewing your changes.', 'caas-control' ),
				'idleJudgment'      => __( 'You\'ve been a sitting duck on this page for 15 minutes. Move on, fella.', 'caas-control' ),
				'fakeSecurity'      => __( 'Suspicious mouse movement detected!', 'caas-control' ),
				'guilt404'          => __( 'I couldn\'t find that page either. Now give me belly rubs.', 'caas-control' ),
				'keyboardTrample'   => __( 'Paws on keyboard!', 'caas-control' ),
				'seoRejection'   => __( 'Fill out your SEO meta description before I let you publish.', 'caas-control' ),
				'scrollPost'            => __( 'Scramble!', 'caas-control' ),
				'scrollPostPreviewHint' => __( 'Click the scratching post (bottom-right) to demo scroll-to-top.', 'caas-control' ),
				'wooCartPreviewLabel'   => __( 'Demo cart', 'caas-control' ),
				'shoo'                 => __( 'Shoo!', 'caas-control' ),
				'shooDismissed'        => __( 'Fine, I\'ll leave.', 'caas-control' ),
				'countdownMinsOnly'    => __( '%s mins left', 'caas-control' ),
				'countdownMinsSeconds' => __( '%1$s mins %2$s sec left', 'caas-control' ),
				'countdownSeconds'     => __( '%s sec left', 'caas-control' ),
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
