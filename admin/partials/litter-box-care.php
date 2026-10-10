<?php
/**
 * Litter Box & Admin Care screen.
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revision_count = Caas_Control_Maintenance::count_post_revisions();
$user_id        = get_current_user_id();
$sidekick       = get_user_meta( $user_id, 'caas_control_sidekick', true );
if ( ! is_string( $sidekick ) || '' === $sidekick ) {
	$sidekick = 'orange_tabby';
}
?>
<div class="wrap caas-control-admin caas-control-panel">
	<h1><?php esc_html_e( 'CaaS: Litter Box & Admin Care', 'caas-control' ); ?></h1>

	<div class="caas-control-card">
		<h2>
			<?php echo Caas_Control_Admin::field_tip( __( 'Deletes old post revisions in bulk to slim the database. Does not remove published content.', 'caas-control' ) ); ?>
			<?php esc_html_e( 'Database Litter Box', 'caas-control' ); ?>
		</h2>
		<p class="caas-control-status">
			<?php
			printf(
				/* translators: %d: revision count */
				esc_html__( 'Your database has accumulated %d old post revisions.', 'caas-control' ),
				(int) $revision_count
			);
			?>
		</p>
		<p><em><?php esc_html_e( 'That’s a lot of old dust bunnies under the rug.', 'caas-control' ); ?></em></p>
		<button type="button" class="button button-primary caas-control-scoop-revisions"><?php esc_html_e( 'Scoop the Litter Box (Clean Revisions)', 'caas-control' ); ?></button>
		<p class="caas-control-ajax-result" id="caas-scoop-result" aria-live="polite"></p>
	</div>

	<div class="caas-control-card">
		<h2>
			<?php echo Caas_Control_Admin::field_tip( __( 'Clears WordPress transients and flushes the object cache if your host provides one.', 'caas-control' ) ); ?>
			<?php esc_html_e( 'Hairball Cache Cleaner', 'caas-control' ); ?>
		</h2>
		<p><?php esc_html_e( 'System cache is currently full of digital fuzz.', 'caas-control' ); ?></p>
		<button type="button" class="button caas-control-clear-cache"><?php esc_html_e( 'Clear the Hairball (Flush Transients & Object Cache)', 'caas-control' ); ?></button>
		<p class="caas-control-ajax-result" id="caas-cache-result" aria-live="polite"></p>
	</div>

	<div class="caas-control-card">
		<h2>
			<?php echo Caas_Control_Admin::field_tip( __( 'Shortcut to WordPress Site Health for PHP version, cron, HTTPS, and other core checks.', 'caas-control' ) ); ?>
			<?php esc_html_e( 'Feline Vet Check (Site Health)', 'caas-control' ); ?>
		</h2>
		<p><?php esc_html_e( 'Whiskers inspected your PHP version, background crons, and SSL.', 'caas-control' ); ?></p>
		<p><strong><?php esc_html_e( 'Vital signs look great—your site is purring at peak performance.', 'caas-control' ); ?></strong></p>
		<a class="button" href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>"><?php esc_html_e( 'Run Full Physical Exam', 'caas-control' ); ?></a>
	</div>

	<div class="caas-control-card">
		<h2>
			<?php echo Caas_Control_Admin::field_tip( __( 'Per-user dashboard personality passed to chaos JavaScript as sidekick—does not change the global agent sprite.', 'caas-control' ) ); ?>
			<?php esc_html_e( 'Active Sidekick Profile Selector', 'caas-control' ); ?>
		</h2>
		<p><?php esc_html_e( 'Choose your dashboard companion style (saved to your user profile):', 'caas-control' ); ?></p>
		<form method="post" action="">
			<?php wp_nonce_field( 'caas_control_sidekick', 'caas_control_sidekick_nonce' ); ?>
			<?php
			$options = array(
				'orange_tabby'    => array(
					'label' => __( 'Orange Tabby (High Energy & Chaos)', 'caas-control' ),
					'tip'   => __( 'Maximum chaos toasts and frequent interruptions.', 'caas-control' ),
				),
				'tuxedo'          => array(
					'label' => __( 'Tuxedo Cat (Strict & Professional)', 'caas-control' ),
					'tip'   => __( 'Slightly more restrained messaging in wp-admin.', 'caas-control' ),
				),
				'sleeping_calico' => array(
					'label' => __( 'Sleeping Calico (Zero Productivity Guaranteed)', 'caas-control' ),
					'tip'   => __( 'Sleepy flavor text—companion still runs if global settings allow.', 'caas-control' ),
				),
			);
			foreach ( $options as $key => $option ) :
				?>
				<label class="caas-control-sidekick-option">
					<input type="radio" name="caas_control_sidekick_choice" value="<?php echo esc_attr( $key ); ?>" <?php checked( $sidekick, $key ); ?> />
					<?php echo Caas_Control_Admin::field_tip( $option['tip'] ); ?>
					<?php echo esc_html( $option['label'] ); ?>
				</label>
			<?php endforeach; ?>
			<?php submit_button( __( 'Save Sidekick', 'caas-control' ), 'secondary', 'submit', false ); ?>
		</form>
	</div>
</div>
