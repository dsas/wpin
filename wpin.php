<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://deansas.org
 * @since             1.0.0
 * @package           Wpin
 *
 * @wordpress-plugin
 * Plugin Name:       WPinboard
 * Plugin URI:        https://github.com/dsas/wpin
 * Description:       WPinboard provides a shortcode and a widget to display bookmarks from https://pinboard.in
 * Version:           1.0.0
 * Author:            Dean Sas
 * Author URI:        https://deansas.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPIN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpin-activator.php
 */
function activate_wpin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpin-activator.php';
	Wpin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpin-deactivator.php
 */
function deactivate_wpin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpin-deactivator.php';
	Wpin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpin' );
register_deactivation_hook( __FILE__, 'deactivate_wpin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wpin() {

	$plugin = new Wpin();
	$plugin->run();

}
run_wpin();
