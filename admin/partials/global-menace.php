<?php
/**
 * Global Menace settings screen.
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = Caas_Control_Settings::get_all();
?>
<div class="wrap caas-control-admin caas-control-panel">
	<h1><?php esc_html_e( 'CaaS: Global Menace Settings', 'caas-control' ); ?></h1>

	<form method="post" action="options.php" class="caas-control-form">
		<?php
		settings_fields( 'caas_control_menace' );
		?>
		<input type="hidden" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[_form_context]" value="menace" />

		<div class="caas-control-section">
			<h2><?php esc_html_e( 'General Chaos Configuration', 'caas-control' ); ?></h2>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Select Your Agent of Chaos', 'caas-control' ); ?></th>
					<td class="caas-control-agents">
						<?php
						$agents = array(
							'cat'   => '🐈 ' . __( 'Cat (Default)', 'caas-control' ),
							'dog'   => '🐕 ' . __( 'Dog', 'caas-control' ),
							'mouse' => '🐁 ' . __( 'Mouse', 'caas-control' ),
							'horse' => '🐎 ' . __( 'Horse', 'caas-control' ),
							'rat'   => '🐀 ' . __( 'Rat', 'caas-control' ),
							'custom'=> '🖼️ ' . __( 'Custom Image', 'caas-control' ),
						);
						foreach ( $agents as $key => $label ) :
							?>
							<label class="caas-control-agent-option">
								<input type="radio" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[agent]" value="<?php echo esc_attr( $key ); ?>" <? checked( $settings['agent'], $key ); ?> />
								<?php echo esc_html( $label ); ?>
							</label>
						<?php endforeach; ?>
						<p class="description">
							<label>
								<?php esc_html_e( 'Custom image URL', 'caas-control' ); ?>
								<input type="url" class="regular-text" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[custom_image_url]" value="<?php echo esc_attr( $settings['custom_image_url'] ); ?>" placeholder="https://..." />
							</label>
							<button type="button" class="button caas-control-upload-image"><?php esc_html_e( 'Upload', 'caas-control' ); ?></button>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="caas_companion_name"><?php esc_html_e( 'Name Your Office Companion', 'caas-control' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="caas_companion_name" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[companion_name]" value="<?php echo esc_attr( $settings['companion_name'] ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="caas_frequency"><?php esc_html_e( 'Chaos Frequency', 'caas-control' ); ?></label></th>
					<td>
						<select id="caas_frequency" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[frequency_minutes]">
							<option value="5" <? selected( (int) $settings['frequency_minutes'], 5 ); ?>><? esc_html_e( 'Every 5 minutes', 'caas-control' ); ?></option>
							<option value="10" <? selected( (int) $settings['frequency_minutes'], 10 ); ?>><? esc_html_e( 'Every 10 minutes', 'caas-control' ); ?></option>
							<option value="15" <? selected( (int) $settings['frequency_minutes'], 15 ); ?>><? esc_html_e( 'Every 15 minutes', 'caas-control' ); ?></option>
							<option value="0" <? selected( (int) $settings['frequency_minutes'], 0 ); ?>><? esc_html_e( 'Whenever blood pressure is too low', 'caas-control' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="caas_duration"><?php esc_html_e( 'Chaos Duration', 'caas-control' ); ?></label></th>
					<td>
						<select id="caas_duration" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[duration_seconds]">
							<option value="5" <? selected( (int) $settings['duration_seconds'], 5 ); ?>><? esc_html_e( '5 seconds', 'caas-control' ); ?></option>
							<option value="10" <? selected( (int) $settings['duration_seconds'], 10 ); ?>><? esc_html_e( '10 seconds', 'caas-control' ); ?></option>
							<option value="30" <? selected( (int) $settings['duration_seconds'], 30 ); ?>><? esc_html_e( 'Until you cry uncle', 'caas-control' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Habitat / Where to Deploy', 'caas-control' ); ?></th>
					<td>
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[habitat_frontend]" value="1" <? checked( $settings['habitat_frontend'] ); ?> /> <? esc_html_e( 'Frontend', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[habitat_backend]" value="1" <? checked( $settings['habitat_backend'] ); ?> /> <? esc_html_e( 'Backend', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[habitat_specific_only]" value="1" <? checked( $settings['habitat_specific_only'] ); ?> /> <? esc_html_e( 'Specific Actions Only', 'caas-control' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Behavior Profile', 'caas-control' ); ?></th>
					<td>
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[behavior_walk]" value="1" <? checked( $settings['behavior_walk'] ); ?> /> <? esc_html_e( 'Walk across screen', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[behavior_sit]" value="1" <? checked( $settings['behavior_sit'] ); ?> /> <? esc_html_e( 'Sit & judge user', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[behavior_follow]" value="1" <? checked( $settings['behavior_follow'] ); ?> /> <? esc_html_e( 'Follow cursor like a stalker', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[behavior_block_mouse]" value="1" <? checked( $settings['behavior_block_mouse'] ); ?> /> <? esc_html_e( 'Block mouse occasionally', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[behavior_meow]" value="1" <? checked( $settings['behavior_meow'] ); ?> /> <? esc_html_e( 'Meow mockingly (bundled GPL cat sound)', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[behavior_random_block]" value="1" <? checked( $settings['behavior_random_block'] ); ?> /> <? esc_html_e( 'Random screen block', 'caas-control' ); ?></label>
					</td>
				</tr>
			</table>
		</div>

		<div class="caas-control-section">
			<h2><?php esc_html_e( 'Chaos Actions & Jumpscares', 'caas-control' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable features', 'caas-control' ); ?></th>
					<td>
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_404_guilt]" value="1" <? checked( $settings['enable_404_guilt'] ); ?> /> <? esc_html_e( '404 Guilt Trip', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_publish_gate]" value="1" <? checked( $settings['enable_publish_gate'] ); ?> /> <? esc_html_e( 'Publish Gatekeeper', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_idle_judgment]" value="1" <? checked( $settings['enable_idle_judgment'] ); ?> /> <? esc_html_e( 'Idle Judgment (frontend)', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_login_hiss]" value="1" <? checked( $settings['enable_login_hiss'] ); ?> /> <? esc_html_e( 'Failed Login Hiss', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_keyboard_trample]" value="1" <? checked( $settings['enable_keyboard_trample'] ); ?> /> <? esc_html_e( 'Keyboard Trampling', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_red_dot]" value="1" <? checked( $settings['enable_red_dot'] ); ?> /> <? esc_html_e( 'Red Dot Torture', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_fake_security]" value="1" <? checked( $settings['enable_fake_security'] ); ?> /> <? esc_html_e( 'Fake Security Alert', 'caas-control' ); ?></label>
					</td>
				</tr>
			</table>
		</div>

		<div class="caas-control-section">
			<h2><?php esc_html_e( 'Frontend Delights', 'caas-control' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Visitor features', 'caas-control' ); ?></th>
					<td>
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_footer_peekaboo]" value="1" <? checked( $settings['enable_footer_peekaboo'] ); ?> /> <? esc_html_e( 'Footer Peekaboo', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_paw_trail]" value="1" <? checked( $settings['enable_paw_trail'] ); ?> /> <? esc_html_e( 'Paw-Print Cursor Trail', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_scroll_post]" value="1" <? checked( $settings['enable_scroll_post'] ); ?> /> <? esc_html_e( 'Scroll-to-Top Scratching Post', 'caas-control' ); ?></label><br />
						<label><input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_woo_cart_wiggle]" value="1" <? checked( $settings['enable_woo_cart_wiggle'] ); ?> /> <? esc_html_e( 'WooCommerce Abandoned Cart Scratching', 'caas-control' ); ?></label>
					</td>
				</tr>
			</table>
		</div>

		<?php submit_button( __( 'Save Menace Settings', 'caas-control' ) ); ?>
	</form>
</div>
