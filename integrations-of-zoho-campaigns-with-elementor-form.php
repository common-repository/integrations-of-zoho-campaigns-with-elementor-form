<?php

/**
 * Plugin Name: Integrations of Zoho Campaigns with Elementor form
 * Plugin URI:  https://formsintegrations.com/elementor-integration-with-zoho-campaigns
 * Description: This plugin integrates Elementor forms with Zoho Campaigns
 * Version:     1.0.2
 * Author:      Forms Integrations
 * Author URI:  https://formsintegrations.com
 * Text Domain: elementor-to-zoho-campaigns
 * Requires PHP: 5.6
 * Domain Path: /languages
 * License: GPLv2 or later
 */

/***
 * If try to direct access  plugin folder it will Exit
 **/
if (!defined('ABSPATH')) {
    exit;
}

// Define most essential constants.
define('IZCEF_VERSION', '1.0.2');
define('IZCEF_DB_VERSION', '1.0.0');
define('IZCEF_PLUGIN_MAIN_FILE', __FILE__);

require_once plugin_dir_path(__FILE__) . 'includes/loader.php';
if (!function_exists('izcef_activate_plugin')) {
    function izcef_activate_plugin()
    {
        global $wp_version;
        if (version_compare($wp_version, '5.1', '<')) {
            wp_die(
                esc_html__('This plugin requires WordPress version 5.1 or higher.', 'elementor-to-zoho-campaigns'),
                esc_html__('Error Activating', 'elementor-to-zoho-campaigns')
            );
        }
        if (version_compare(PHP_VERSION, '5.6.0', '<')) {
            wp_die(
                esc_html__('Forms Integrations requires PHP version 5.6.', 'elementor-to-zoho-campaigns'),
                esc_html__('Error Activating', 'elementor-to-zoho-campaigns')
            );
        }
        do_action('izcef_activation');
    }
}

register_activation_hook(__FILE__, 'izcef_activate_plugin');

if (!function_exists('izcef_deactivation')) {
    function izcef_deactivation()
    {
        do_action('izcef_deactivation');
    }
}
register_deactivation_hook(__FILE__, 'izcef_deactivation');

if (!function_exists('izcef_uninstall_plugin')) {
    function izcef_uninstall_plugin()
    {
        do_action('izcef_uninstall');
    }
}
register_uninstall_hook(__FILE__, 'izcef_uninstall_plugin');
