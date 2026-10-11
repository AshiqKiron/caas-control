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

	<form
		method="post"
		action="options.php"
		class="caas-control-form"
		data-caas-cat-media-url="<?php echo esc_url( Caas_Control_Settings::default_cat_media_url() ); ?>"
		data-caas-judging-media-url="<?php echo esc_url( Caas_Control_Settings::judging_video_url() ); ?>"
	>
		<?php
		settings_fields( 'caas_control_menace' );
		?>
		<input type="hidden" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[_form_context]" value="menace" />

		<div class="caas-control-section">
			<h2><?php esc_html_e( 'Global CaaS Control', 'caas-control' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<?php echo Caas_Control_Admin::field_tip( __( 'When off, the plugin stays installed and settings remain saved, but no CaaS behavior runs on the front end, back end, or login screen.', 'caas-control' ) ); ?>
						<?php esc_html_e( 'Enable CaaS Control', 'caas-control' ); ?>
					</th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[caas_enabled]" value="1" <?php checked( $settings['caas_enabled'] ); ?> />
							<?php esc_html_e( 'Turn on chaos companions, jumpscares, and related features site-wide', 'caas-control' ); ?>
						</label>
					</td>
				</tr>
			</table>
		</div>

		<div class="caas-control-section">
			<h2><?php esc_html_e( 'General Chaos Configuration', 'caas-control' ); ?></h2>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<?php echo Caas_Control_Admin::field_tip( __( 'Pick the sprite or emoji that represents your companion. Custom Pawtrol officer uses the URL below and overrides bundled agents.', 'caas-control' ) ); ?>
						<?php esc_html_e( 'Select Your Agent of Chaos', 'caas-control' ); ?>
					</th>
					<td class="caas-control-agents">
						<?php
						$agents = array(
							'cat'    => array(
								'label' => __( 'The Pawtrol officer (Default)', 'caas-control' ),
								'tip'   => __( 'Bundled GPL cat sprite (PNG, or gpl-cat.mp4/webm in assets/gpl-cat/) and meow sound.', 'caas-control' ),
							),
							'custom' => array(
								'label' => __( 'Custom Pawtrol officer', 'caas-control' ),
								'tip'   => __( 'Use your own PNG or GIF URL from the media library or any HTTPS link.', 'caas-control' ),
							),
						);
						foreach ( $agents as $key => $agent ) :
							?>
							<label class="caas-control-agent-option">
								<input type="radio" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[agent]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $settings['agent'], $key ); ?> />
								<?php echo Caas_Control_Admin::field_tip( $agent['tip'] ); ?>
								<?php echo esc_html( $agent['label'] ); ?>
							</label>
						<?php endforeach; ?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php echo Caas_Control_Admin::field_tip( __( 'Shown in accessibility labels and occasional on-screen toasts.', 'caas-control' ) ); ?>
						<label for="caas_companion_name"><?php esc_html_e( 'Name Your Office Companion', 'caas-control' ); ?></label>
					</th>
					<td>
						<input type="text" class="regular-text caas-control-companion-name-input" id="caas_companion_name" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[companion_name]" value="<?php echo esc_attr( $settings['companion_name'] ); ?>" />
					</td>
				</tr>
				<tr class="caas-control-custom-agent-fields<?php echo ( 'custom' !== $settings['agent'] ) ? ' is-hidden' : ''; ?>">
					<th scope="row">
						<?php echo Caas_Control_Admin::field_tip( __( 'Required when Custom Pawtrol officer is selected. Square images around 128px work best.', 'caas-control' ) ); ?>
						<label for="caas_custom_image_url"><?php esc_html_e( 'Custom Pawtrol officer URL', 'caas-control' ); ?></label>
					</th>
					<td>
						<input type="url" class="regular-text caas-control-custom-image-url" id="caas_custom_image_url" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[custom_image_url]" value="<?php echo esc_attr( $settings['custom_image_url'] ); ?>" placeholder="https://..." />
						<button type="button" class="button caas-control-upload-image"><?php esc_html_e( 'Upload', 'caas-control' ); ?></button>
					</td>
				</tr>
			</table>
		</div>

		<div class="caas-control-section">
			<h2><?php esc_html_e( 'Pawdructivity', 'caas-control' ); ?></h2>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<?php echo Caas_Control_Admin::field_tip( __( 'How long to wait after the page loads before the first timed companion behavior, and how long between each run. Adjustable from 5 minutes to 5 hours in 5-minute steps.', 'caas-control' ) ); ?>
						<label for="caas_appear_after"><?php esc_html_e( 'Cat appears after', 'caas-control' ); ?></label>
					</th>
					<td>
						<?php
						$appear_val     = (int) $settings['appear_after_seconds'];
						$appear_min     = Caas_Control_Settings::appear_after_seconds_min();
						$appear_max     = Caas_Control_Settings::appear_after_seconds_max();
						$appear_step    = Caas_Control_Settings::appear_after_seconds_step();
						$appear_display = Caas_Control_Settings::appear_after_slider_display( $appear_val );
						?>
						<div class="caas-control-duration-slider">
							<div class="caas-control-duration-slider__track">
								<input
									type="range"
									class="caas-control-appear-after-range"
									id="caas_appear_after"
									name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[appear_after_seconds]"
									min="<?php echo esc_attr( (string) $appear_min ); ?>"
									max="<?php echo esc_attr( (string) $appear_max ); ?>"
									step="<?php echo esc_attr( (string) $appear_step ); ?>"
									value="<?php echo esc_attr( (string) $appear_val ); ?>"
									aria-valuemin="<?php echo esc_attr( (string) $appear_min ); ?>"
									aria-valuemax="<?php echo esc_attr( (string) $appear_max ); ?>"
									aria-valuenow="<?php echo esc_attr( (string) $appear_val ); ?>"
								/>
							</div>
							<output class="caas-control-duration-value" for="caas_appear_after">
								<span class="caas-control-duration-value__num"><?php echo esc_html( $appear_display['display'] ); ?></span>
								<span class="caas-control-duration-value__suffix<?php echo $appear_display['use_seconds_suffix'] ? '' : ' is-hidden'; ?>"><?php esc_html_e( 'seconds', 'caas-control' ); ?></span>
							</output>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php echo Caas_Control_Admin::field_tip( __( 'How long each timed behavior animation stays on screen before the companion leaves.', 'caas-control' ) ); ?>
						<label for="caas_duration"><?php esc_html_e( 'Chaos Duration', 'caas-control' ); ?></label>
					</th>
					<td>
						<?php
						$duration_val     = (int) $settings['duration_seconds'];
						$duration_min     = Caas_Control_Settings::duration_seconds_min();
						$duration_max     = Caas_Control_Settings::duration_seconds_max();
						$duration_step    = Caas_Control_Settings::duration_seconds_step();
						$duration_display = Caas_Control_Settings::duration_slider_display( $duration_val );
						?>
						<div class="caas-control-duration-slider">
							<div class="caas-control-duration-slider__track">
								<input
									type="range"
									class="caas-control-duration-range"
									id="caas_duration"
									name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[duration_seconds]"
									min="<?php echo esc_attr( (string) $duration_min ); ?>"
									max="<?php echo esc_attr( (string) $duration_max ); ?>"
									step="<?php echo esc_attr( (string) $duration_step ); ?>"
									value="<?php echo esc_attr( (string) $duration_val ); ?>"
									aria-valuemin="<?php echo esc_attr( (string) $duration_min ); ?>"
									aria-valuemax="<?php echo esc_attr( (string) $duration_max ); ?>"
									aria-valuenow="<?php echo esc_attr( (string) $duration_val ); ?>"
								/>
							</div>
							<output class="caas-control-duration-value" for="caas_duration">
								<span class="caas-control-duration-value__num"><?php echo esc_html( $duration_display['display'] ); ?></span>
								<span class="caas-control-duration-value__suffix<?php echo $duration_display['use_seconds_suffix'] ? '' : ' is-hidden'; ?>"><?php esc_html_e( 'seconds', 'caas-control' ); ?></span>
							</output>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php echo Caas_Control_Admin::field_tip( __( 'Runs a walk-across demo with your current agent, name, and chaos duration—save settings separately if you want them to stick.', 'caas-control' ) ); ?>
						<?php esc_html_e( 'Preview companion', 'caas-control' ); ?>
					</th>
					<td>
						<?php echo Caas_Control_Admin::behavior_preview_button( 'walk' ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php echo Caas_Control_Admin::field_tip( __( 'Controls where chaos scripts and the companion load. Backend includes wp-admin and the block editor when enabled.', 'caas-control' ) ); ?>
						<?php esc_html_e( 'Habitat / Where to Deploy', 'caas-control' ); ?>
					</th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[habitat_frontend]" value="1" <?php checked( $settings['habitat_frontend'] ); ?> />
							<?php echo Caas_Control_Admin::field_tip( __( 'Public site for logged-in and guest visitors.', 'caas-control' ) ); ?>
							<?php esc_html_e( 'Frontend', 'caas-control' ); ?>
						</label><br />
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[habitat_backend]" value="1" <?php checked( $settings['habitat_backend'] ); ?> />
							<?php echo Caas_Control_Admin::field_tip( __( 'WordPress dashboard and editor screens.', 'caas-control' ) ); ?>
							<?php esc_html_e( 'Backend', 'caas-control' ); ?>
						</label><br />
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[habitat_specific_only]" value="1" <?php checked( $settings['habitat_specific_only'] ); ?> />
							<?php echo Caas_Control_Admin::field_tip( __( 'In wp-admin, limit chaos to CaaS settings pages and the block editor instead of every admin screen.', 'caas-control' ) ); ?>
							<?php esc_html_e( 'Specific Actions Only', 'caas-control' ); ?>
						</label>
					</td>
				</tr>
			</table>
		</div>

		<div class="caas-control-section">
			<h2><?php esc_html_e( 'Chaos Actions & Jumpscares', 'caas-control' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<?php echo Caas_Control_Admin::field_tip( __( 'Event-driven pranks and admin/front surprises—not the same as scheduled companion walk/sit/follow antics. Use Preview to demo each effect on this screen (uses current agent, duration, and unsaved checkbox state).', 'caas-control' ) ); ?>
						<?php esc_html_e( 'Enable features', 'caas-control' ); ?>
					</th>
					<td>
						<span class="caas-control-feature-row">
							<label>
								<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_404_guilt]" value="1" <?php checked( $settings['enable_404_guilt'] ); ?> />
								<?php echo Caas_Control_Admin::field_tip( __( 'Adds a playful guilt message on 404 pages.', 'caas-control' ) ); ?>
								<?php esc_html_e( '404 Guilt Trip', 'caas-control' ); ?>
							</label>
							<?php echo Caas_Control_Admin::feature_preview_button( 'guilt404' ); ?>
						</span><br />
						<span class="caas-control-feature-row">
							<label>
								<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_publish_gate]" value="1" <?php checked( $settings['enable_publish_gate'] ); ?> />
								<?php echo Caas_Control_Admin::field_tip( __( 'Extra confirmation step before publishing in the block editor.', 'caas-control' ) ); ?>
								<?php esc_html_e( 'Publish Gatekeeper', 'caas-control' ); ?>
							</label>
							<?php echo Caas_Control_Admin::feature_preview_button( 'publishGate' ); ?>
						</span><br />
						<span class="caas-control-feature-row">
							<label>
								<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_idle_judgment]" value="1" <?php checked( $settings['enable_idle_judgment'] ); ?> />
								<?php echo Caas_Control_Admin::field_tip( __( 'Toast when a visitor stops scrolling or moving the mouse for a while.', 'caas-control' ) ); ?>
								<?php esc_html_e( 'Idle Judgment (frontend)', 'caas-control' ); ?>
							</label>
							<?php echo Caas_Control_Admin::feature_preview_button( 'idleJudgment' ); ?>
						</span><br />
						<span class="caas-control-feature-row">
							<label>
								<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_login_hiss]" value="1" <?php checked( $settings['enable_login_hiss'] ); ?> />
								<?php echo Caas_Control_Admin::field_tip( __( 'Visual hiss on wp-login.php after a failed login attempt.', 'caas-control' ) ); ?>
								<?php esc_html_e( 'Failed Login Hiss', 'caas-control' ); ?>
							</label>
							<?php echo Caas_Control_Admin::feature_preview_button( 'loginHiss' ); ?>
						</span><br />
						<span class="caas-control-feature-row">
							<label>
								<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_keyboard_trample]" value="1" <?php checked( $settings['enable_keyboard_trample'] ); ?> />
								<?php echo Caas_Control_Admin::field_tip( __( 'Random fake keystrokes toast in the editor after idle time.', 'caas-control' ) ); ?>
								<?php esc_html_e( 'Keyboard Trampling', 'caas-control' ); ?>
							</label>
							<?php echo Caas_Control_Admin::feature_preview_button( 'keyboardTrample' ); ?>
						</span><br />
						<span class="caas-control-feature-row">
							<label>
								<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_red_dot]" value="1" <?php checked( $settings['enable_red_dot'] ); ?> />
								<?php echo Caas_Control_Admin::field_tip( __( 'Laser dot teases the companion; clicking it triggers a reaction.', 'caas-control' ) ); ?>
								<?php esc_html_e( 'Red Dot Torture', 'caas-control' ); ?>
							</label>
							<?php echo Caas_Control_Admin::feature_preview_button( 'redDot' ); ?>
						</span><br />
						<span class="caas-control-feature-row">
							<label>
								<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_fake_security]" value="1" <?php checked( $settings['enable_fake_security'] ); ?> />
								<?php echo Caas_Control_Admin::field_tip( __( 'Spoof security popup—clearly joke content, not a real scan.', 'caas-control' ) ); ?>
								<?php esc_html_e( 'Fake Security Alert', 'caas-control' ); ?>
							</label>
							<?php echo Caas_Control_Admin::feature_preview_button( 'fakeSecurity' ); ?>
						</span>
					</td>
				</tr>
			</table>
		</div>

		<div class="caas-control-section">
			<h2><?php esc_html_e( 'Frontend Delights', 'caas-control' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<?php echo Caas_Control_Admin::field_tip( __( 'Optional front-end-only extras that do not require the companion timer. Use Preview to demo each effect on this screen (uses current agent and unsaved checkbox state).', 'caas-control' ) ); ?>
						<?php esc_html_e( 'Visitor features', 'caas-control' ); ?>
					</th>
					<td>
						<span class="caas-control-feature-row">
							<label>
								<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_footer_peekaboo]" value="1" <?php checked( $settings['enable_footer_peekaboo'] ); ?> />
								<?php echo Caas_Control_Admin::field_tip( __( 'Companion peeks up from the bottom when the user scrolls near the footer.', 'caas-control' ) ); ?>
								<?php esc_html_e( 'Footer Peekaboo', 'caas-control' ); ?>
							</label>
							<?php echo Caas_Control_Admin::feature_preview_button( 'footerPeekaboo' ); ?>
						</span><br />
						<span class="caas-control-feature-row">
							<label>
								<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_paw_trail]" value="1" <?php checked( $settings['enable_paw_trail'] ); ?> />
								<?php echo Caas_Control_Admin::field_tip( __( 'Chunky paw prints bounce along behind the cursor on the public site.', 'caas-control' ) ); ?>
								<?php esc_html_e( 'Paw-Print Cursor Trail', 'caas-control' ); ?>
							</label>
							<?php echo Caas_Control_Admin::feature_preview_button( 'pawTrail' ); ?>
						</span><br />
						<span class="caas-control-feature-row">
							<label>
								<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_scroll_post]" value="1" <?php checked( $settings['enable_scroll_post'] ); ?> />
								<?php echo Caas_Control_Admin::field_tip( __( 'Floating scratching post: your companion claws it while the page scrolls back to the top.', 'caas-control' ) ); ?>
								<?php esc_html_e( 'Scroll-to-Top Scratching Post', 'caas-control' ); ?>
							</label>
							<?php echo Caas_Control_Admin::feature_preview_button( 'scrollPost' ); ?>
						</span><br />
						<span class="caas-control-feature-row">
							<label>
								<input type="checkbox" name="<?php echo esc_attr( Caas_Control_Settings::OPTION_KEY ); ?>[enable_woo_cart_wiggle]" value="1" <?php checked( $settings['enable_woo_cart_wiggle'] ); ?> />
								<?php echo Caas_Control_Admin::field_tip( __( 'When WooCommerce is active, cart UI wiggles if items sit idle—nudge to checkout.', 'caas-control' ) ); ?>
								<?php esc_html_e( 'WooCommerce Abandoned Cart Scratching', 'caas-control' ); ?>
							</label>
							<?php echo Caas_Control_Admin::feature_preview_button( 'wooCartWiggle' ); ?>
						</span>
					</td>
				</tr>
			</table>
		</div>

		<?php submit_button( __( 'Save Menace Settings', 'caas-control' ) ); ?>
	</form>
</div>
