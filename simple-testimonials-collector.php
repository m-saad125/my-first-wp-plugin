<?php
/**
 * Plugin Name: Simple Testimonials Collector
 * Plugin URI:  https://dnnengineer.com/simple-testimonials-collector
 * Description: A simple plugin to collect and display testimonials.
 * Version:     1.0.0
 * Author:      Saad
 * Author URI:  https://dnnengineer.com
 * License:     GPL-2.0+
 * Text Domain: simple-testimonials-collector
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants
define( 'STC_VERSION', '1.0.0' );
define( 'STC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'STC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-stc-loader.php';

/**
 * Begins execution of the plugin.
 */
function run_simple_testimonials_collector() {
	$plugin = new STC_Loader();
	$plugin->run();
}
run_simple_testimonials_collector();
