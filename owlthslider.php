<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://owlth.tech
 * @since             1.0.0
 * @package           Owlthslider
 *
 * @wordpress-plugin
 * Plugin Name:       OwlthSlider
 * Plugin URI:        https://owlth.tech
 * Description:       SEO Friendly Light Weight WP Slider
 * Version:           1.0.0
 * Author:            Owlth Tech
 * Author URI:        https://owlth.tech/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       owlthslider
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if (defined('WP_DEBUG') && WP_DEBUG) {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'OWLTHSLIDER_VERSION', '1.0.1' );
define( 'OWLTHSLIDER_PLUGIN_DIR', plugin_dir_path( __FILE__) );
define('OWLTHSLIDER_PLUGIN_URL' , plugin_dir_url( __FILE__ ));
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-owlthslider-activator.php
 */
function activate_owlthslider() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-owlthslider-activator.php';
	Owlthslider_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-owlthslider-deactivator.php
 */
function deactivate_owlthslider() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-owlthslider-deactivator.php';
	Owlthslider_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_owlthslider' );
register_deactivation_hook( __FILE__, 'deactivate_owlthslider' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-owlthslider.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_owlthslider() {

	$plugin = new Owlthslider();
	$plugin->run();

}
run_owlthslider();
