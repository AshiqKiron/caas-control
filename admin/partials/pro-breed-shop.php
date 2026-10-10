<?php
/**
 * Pro & Breed Shop screen.
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = Caas_Control_Settings::get_all();
$is_pro   = ! empty( $settings['pro_enabled'] );
?>
<div class="wrap caas-control-admin caas-control-panel">
	<h1><?php esc_html_e( 'CaaS: Pro & Breed Shop', 'caas-control' ); ?></h1>

	<div class="caas-control-pro-banner">
		<h2><?php esc_html_e( 'Upgrade to CaaS Pro', 'caas-control' ); ?></h2>
		<p><?php esc_html_e( 'Unlock advanced skins, ASMR purring sounds, and agency white-labeling!', 'caas-control' ); ?></p>
	</div>

	<table class="widefat caas-control-pro-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Feature', 'caas-control' ); ?></th>
				<th><?php esc_html_e( 'Status', 'caas-control' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<strong>
						<?php echo Caas_Control_Admin::field_tip( __( 'Swap your cat for Nyan Cat, Grumpy Cat clone, or a Tuxedo judge.', 'caas-control' ) ); ?>
						<?php esc_html_e( 'Breed & Skin Packs', 'caas-control' ); ?>
					</strong>
					<p class="description"><?php esc_html_e( 'Swap your cat for Nyan Cat, Grumpy Cat clone, or a Tuxedo judge.', 'caas-control' ); ?></p>
				</td>
				<td><?php echo $is_pro ? esc_html__( 'Active / Installed', 'caas-control' ) : esc_html__( 'Free tier', 'caas-control' ); ?></td>
				<td><button type="button" class="button caas-control-pro-upgrade" data-feature="skins"><?php esc_html_e( 'Upgrade', 'caas-control' ); ?></button></td>
			</tr>
			<tr>
				<td>
					<strong>
						<?php echo Caas_Control_Admin::field_tip( __( 'High-definition ASMR purrs and meows for clicks and alerts.', 'caas-control' ) ); ?>
						<?php esc_html_e( 'Purr-ductivity Audio Pack', 'caas-control' ); ?>
					</strong>
					<p class="description"><?php esc_html_e( 'High-definition ASMR purrs & meows for clicks and alerts.', 'caas-control' ); ?></p>
				</td>
				<td><?php echo $is_pro ? esc_html__( 'Installed', 'caas-control' ) : esc_html__( 'GPL meow included', 'caas-control' ); ?></td>
				<td><button type="button" class="button caas-control-pro-upgrade" data-feature="audio"><?php esc_html_e( 'Upgrade', 'caas-control' ); ?></button></td>
			</tr>
			<tr>
				<td>
					<strong>
						<?php echo Caas_Control_Admin::field_tip( __( 'Replace the cat with your own client mascot or logo.', 'caas-control' ) ); ?>
						<?php esc_html_e( 'Agency White-Label Mode', 'caas-control' ); ?>
					</strong>
					<p class="description"><?php esc_html_e( 'Replace the cat with your own client mascot or logo.', 'caas-control' ); ?></p>
				</td>
				<td><?php echo $is_pro ? esc_html__( 'Enabled', 'caas-control' ) : '—'; ?></td>
				<td><button type="button" class="button caas-control-pro-upgrade" data-feature="whitelabel"><?php esc_html_e( 'Upgrade', 'caas-control' ); ?></button></td>
			</tr>
		</tbody>
	</table>

	<div class="caas-control-card caas-control-tip-jar">
		<h2>
			<?php echo Caas_Control_Admin::field_tip( __( 'Optional support links—does not change plugin behavior.', 'caas-control' ) ); ?>
			<?php esc_html_e( 'Feed the Developer (Tip Jar)', 'caas-control' ); ?>
		</h2>
		<p><?php esc_html_e( 'Buy the digital cat a virtual fish or a can of tuna so it stops knocking your coffee cup off the desk.', 'caas-control' ); ?></p>
		<p>
			<a class="button" href="https://buymeacoffee.com/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Feed $5 (1 Tuna Can)', 'caas-control' ); ?></a>
			<a class="button button-primary" href="https://buymeacoffee.com/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Feed $15 (Full Sushi Feast)', 'caas-control' ); ?></a>
		</p>
	</div>

	<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<form method="post" action="options.php" class="caas-control-dev-toggle">
			<?php settings_fields( 'caas_control_pro_dev' ); ?>
			<input type="hidden" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[_form_context]" value="pro" />
			<label>
				<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[pro_enabled]" value="1" <?php checked( $is_pro ); ?> />
				<?php echo Caas_Control_Admin::field_tip( __( 'Turns on proEnabled in JavaScript config for testing without a license server.', 'caas-control' ) ); ?>
				<?php esc_html_e( 'Developer: simulate Pro unlock on this site', 'caas-control' ); ?>
			</label>
			<?php submit_button( __( 'Save Pro Simulation', 'caas-control' ), 'secondary', 'submit', false ); ?>
		</form>
	<?php endif; ?>
</div>
