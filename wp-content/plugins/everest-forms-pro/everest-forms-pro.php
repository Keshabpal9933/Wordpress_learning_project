<?php
/**
 * Plugin Name: Everest Forms (Pro)
 * Plugin URI: https://wpeverest.com/wordpress-plugins/everest-forms/
 * Description: Everest Forms features and extensions controller.
 * Version: 1.3.4.1
 * Author: WPEverest
 * Author URI: https://wpeverest.com
 * Text Domain: everest-forms-pro
 * Domain Path: /languages/
 * EVF requires at least: 1.6.0
 * EVF tested up to: 1.6.7
 *
 * @package EverestForms_Pro
 */

defined( 'ABSPATH' ) || exit;

// Define EFP_PLUGIN_FILE.
if ( ! defined( 'EFP_PLUGIN_FILE' ) ) {
	define( 'EFP_PLUGIN_FILE', __FILE__ );
}

// Include the main EverestForms_Pro class.
if ( ! class_exists( 'EverestForms_Pro' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-everest-forms-pro.php';
}

// Include the main EVF_Plugin_Updater class.
if ( ! class_exists( 'EVF_Plugin_Updater' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-evf-plugin-updater.php';
}

// Initialize the plugin.
add_action( 'plugins_loaded', array( 'EverestForms_Pro', 'get_instance' ), 0 );
