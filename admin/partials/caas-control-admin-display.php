<?php
/**
 * Admin settings screen markup.
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap caas-control-admin">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<p><?php esc_html_e( 'Configure CaaS Control settings here.', 'caas-control' ); ?></p>
</div>
