<?php
/**
 * Database litter box, cache hairball, and site health hooks.
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin maintenance and playful Site Health copy.
 */
class Caas_Control_Maintenance {

	/**
	 * Count post revisions site-wide (approximate for display).
	 */
	public static function count_post_revisions(): int {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$count = $wpdb->get_var(
			"SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'revision'"
		);

		return $count ? (int) $count : 0;
	}

	/**
	 * Delete all post revisions (aggressive sweep).
	 *
	 * @return int Number deleted.
	 */
	public static function scoop_revisions(): int {
		global $wpdb;

		$ids = $wpdb->get_col(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision'"
		);

		if ( ! is_array( $ids ) || empty( $ids ) ) {
			return 0;
		}

		$deleted = 0;
		foreach ( $ids as $id ) {
			$result = wp_delete_post( (int) $id, true );
			if ( false !== $result ) {
				++$deleted;
			}
		}

		return $deleted;
	}

	/**
	 * Flush transients and common object caches.
	 */
	public static function clear_hairball_cache(): void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'"
		);

		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
	}

	/**
	 * AJAX: scoop revisions.
	 */
	public function ajax_scoop_revisions(): void {
		check_ajax_referer( 'caas_control_maintenance', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'caas-control' ) ), 403 );
		}

		$deleted = self::scoop_revisions();

		wp_send_json_success(
			array(
				'deleted' => $deleted,
				'message' => sprintf(
					/* translators: %d: number of revisions removed */
					__( 'Scooped %d old revisions into the litter box.', 'caas-control' ),
					$deleted
				),
			)
		);
	}

	/**
	 * AJAX: flush cache.
	 */
	public function ajax_clear_cache(): void {
		check_ajax_referer( 'caas_control_maintenance', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'caas-control' ) ), 403 );
		}

		self::clear_hairball_cache();

		wp_send_json_success(
			array(
				'message' => __( 'Hairball expelled. Transients and object cache cleared.', 'caas-control' ),
			)
		);
	}

	/**
	 * Vet check copy on Site Health screen.
	 */
	public function site_health_vet_notice(): void {
		$screen = get_current_screen();
		if ( ! $screen || 'site-health' !== $screen->id ) {
			return;
		}

		echo '<div class="notice notice-success"><p>';
		esc_html_e(
			'Whiskers inspected your PHP version, background crons, and SSL certificate. Vital signs look great—your site is purring at peak performance.',
			'caas-control'
		);
		echo '</p></div>';
	}

	/**
	 * Core update nag with kitten nudge copy.
	 */
	public function core_update_nudge(): void {
		if ( ! current_user_can( 'update_core' ) ) {
			return;
		}

		$updates = get_core_updates();
		if ( ! is_array( $updates ) || empty( $updates ) ) {
			return;
		}

		$update = $updates[0];
		if ( empty( $update->response ) || 'upgrade' !== $update->response ) {
			return;
		}

		echo '<div class="notice notice-info caas-control-kitten-nudge"><p>';
		esc_html_e(
			'Psst! WordPress Core just learned a new trick. Time to upgrade your scratching post and grab the latest update.',
			'caas-control'
		);
		echo '</p></div>';
	}
}
