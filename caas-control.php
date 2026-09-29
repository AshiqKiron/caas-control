<?php
/**
 * Plugin Name:       CaaS Control
 * Plugin URI:        https://example.com/caas-control
 * Description:       Control and manage CaaS (Content as a Service) integrations for WordPress.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       caas-control
 * Domain Path:       /languages
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CAAS_CONTROL_VERSION', '1.0.0' );
define( 'CAAS_CONTROL_PLUGIN_FILE', __FILE__ );
define( 'CAAS_CONTROL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAAS_CONTROL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CAAS_CONTROL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once CAAS_CONTROL_PLUGIN_DIR . 'includes/class-caas-control-loader.php';
require_once CAAS_CONTROL_PLUGIN_DIR . 'includes/class-caas-control-activator.php';
require_once CAAS_CONTROL_PLUGIN_DIR . 'includes/class-caas-control-deactivator.php';
require_once CAAS_CONTROL_PLUGIN_DIR . 'includes/class-caas-control.php';

/**
 * Runs on plugin activation.
 */
function caas_control_activate(): void {
	Caas_Control_Activator::activate();
}

/**
 * Runs on plugin deactivation.
 */
function caas_control_deactivate(): void {
	Caas_Control_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'caas_control_activate' );
register_deactivation_hook( __FILE__, 'caas_control_deactivate' );

/**
 * Begins plugin execution.
 */
function caas_control_run(): Caas_Control {
	$plugin = new Caas_Control();
	$plugin->run();
	return $plugin;
}

caas_control_run();
