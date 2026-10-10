<?php
/**
 * Chaos feature hooks (404, login, WSOD, profiles, WooCommerce).
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers behavioral WordPress hooks from the spec.
 */
class Caas_Control_Features {

	/**
	 * 404 guilt trip message.
	 */
	public function render_404_guilt(): void {
		$settings = Caas_Control_Settings::get_all();
		if ( empty( $settings['enable_404_guilt'] ) || ! is_404() ) {
			return;
		}

		add_action( 'wp_footer', array( $this, 'output_404_guilt_banner' ), 5 );
	}

	/**
	 * Footer banner on 404 pages.
	 */
	public function output_404_guilt_banner(): void {
		echo '<div class="caas-control-404-guilt"><p>' . esc_html__(
			'I couldn\'t find that page either. Now give me belly rubs.',
			'caas-control'
		) . '</p></div>';
	}

	/**
	 * Login screen hiss after failed attempt.
	 */
	public function enqueue_login_assets(): void {
		$settings = Caas_Control_Settings::get_all();
		if ( empty( $settings['enable_login_hiss'] ) ) {
			return;
		}

		$failed = isset( $_GET['login'] ) && 'failed' === $_GET['login']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $failed ) {
			return;
		}

		wp_enqueue_style(
			'caas-control-public',
			CAAS_CONTROL_PLUGIN_URL . 'public/css/caas-control-public.css',
			array(),
			CAAS_CONTROL_VERSION
		);

		wp_enqueue_script(
			'caas-control-chaos',
			CAAS_CONTROL_PLUGIN_URL . 'public/js/caas-control-chaos.js',
			array(),
			CAAS_CONTROL_VERSION,
			true
		);

		wp_localize_script(
			'caas-control-chaos',
			'caasControlChaos',
			array_merge(
				Caas_Control_Settings::chaos_config_for_js(),
				array(
					'context'     => 'login',
					'loginFailed' => true,
				)
			)
		);
	}

	/**
	 * Friendly fatal error / WSOD handler when possible.
	 */
	public function register_wsod_handler(): void {
		register_shutdown_function( array( $this, 'maybe_wsod_cat' ) );
	}

	/**
	 * Shutdown: show cat among code if fatal.
	 */
	public function maybe_wsod_cat(): void {
		$error = error_get_last();
		if ( ! is_array( $error ) ) {
			return;
		}

		$fatal = array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR );
		if ( ! in_array( $error['type'], $fatal, true ) ) {
			return;
		}

		if ( headers_sent() ) {
			return;
		}

		status_header( 500 );
		nocache_headers();

		$message = esc_html__(
			'Oops, I knocked the server off the table.',
			'caas-control'
		);

		echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>CaaS Control</title>';
		echo '<style>body{font-family:system-ui,sans-serif;background:#1e1e2e;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}.box{text-align:center;max-width:520px;padding:2rem}.cat{font-size:4rem}.code{font-size:.75rem;opacity:.6;margin-top:1rem;word-break:break-all}</style></head><body>';
		echo '<div class="box"><div class="cat">🐱</div><h1>' . esc_html( $message ) . '</h1>';
		echo '<p class="code">' . esc_html( $error['message'] ) . '</p></div></body></html>';
		exit;
	}

	/**
	 * User profile: office companion selector.
	 *
	 * @param WP_User $user User object.
	 */
	public function render_profile_sidekick( WP_User $user ): void {
		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			return;
		}

		$value = get_user_meta( $user->ID, 'caas_control_sidekick', true );
		if ( ! is_string( $value ) || '' === $value ) {
			$value = 'orange_tabby';
		}

		$options = array(
			'orange_tabby'    => __( 'Orange Tabby (High Energy & Chaos)', 'caas-control' ),
			'tuxedo'          => __( 'Tuxedo Cat (Strict & Professional)', 'caas-control' ),
			'sleeping_calico' => __( 'Sleeping Calico (Zero Productivity Guaranteed)', 'caas-control' ),
		);
		?>
		<h2><?php esc_html_e( 'CaaS Office Companion', 'caas-control' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="caas_control_sidekick"><? esc_html_e( 'Dashboard sidekick style', 'caas-control' ); ?></label></th>
				<td>
					<?php foreach ( $options as $key => $label ) : ?>
						<label style="display:block;margin-bottom:.5em;">
							<input type="radio" name="caas_control_sidekick" id="caas_control_sidekick_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php checked( $value, $key ); ?> />
							<?php echo esc_html( $label ); ?>
						</label>
					<?php endforeach; ?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save profile sidekick.
	 *
	 * @param int $user_id User ID.
	 */
	public function save_profile_sidekick( int $user_id ): void {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		$allowed = array( 'orange_tabby', 'tuxedo', 'sleeping_calico' );
		$raw     = isset( $_POST['caas_control_sidekick'] ) ? sanitize_text_field( wp_unslash( $_POST['caas_control_sidekick'] ) ) : 'orange_tabby';

		if ( in_array( $raw, $allowed, true ) ) {
			update_user_meta( $user_id, 'caas_control_sidekick', $raw );
		}
	}

	/**
	 * WooCommerce order thank-you celebration flag.
	 */
	public function woo_order_celebration(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		add_action(
			'woocommerce_thankyou',
			static function ( $order_id ): void {
				if ( ! $order_id ) {
					return;
				}
				wp_enqueue_script(
					'caas-control-chaos',
					CAAS_CONTROL_PLUGIN_URL . 'public/js/caas-control-chaos.js',
					array(),
					CAAS_CONTROL_VERSION,
					true
				);
				wp_localize_script(
					'caas-control-chaos',
					'caasControlChaos',
					array_merge(
						Caas_Control_Settings::chaos_config_for_js(),
						array(
							'context' => 'woo_thankyou',
							'orderId' => (int) $order_id,
						)
					)
				);
			}
		);
	}
}
