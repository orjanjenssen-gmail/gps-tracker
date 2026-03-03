<?php
/**
 * Plugin Name: GPS Tracker
 * Description: Receives GPS data via REST API and displays positions on maps.
 * Version: 1.8
 * Author: Ørjan Jenssen
 */

defined('ABSPATH') or exit;

/*
|--------------------------------------------------------------------------
| Bootstrap / Version
|--------------------------------------------------------------------------
*/

require_once plugin_dir_path(__FILE__) . 'includes/version.php';

/*
|--------------------------------------------------------------------------
| Includes
|--------------------------------------------------------------------------
*/

require_once plugin_dir_path(__FILE__) . 'includes/database.php';
require_once plugin_dir_path(__FILE__) . 'includes/utils.php';
require_once plugin_dir_path(__FILE__) . 'includes/auth.php';
require_once plugin_dir_path(__FILE__) . 'includes/endpoints.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-ui.php';

/*
|--------------------------------------------------------------------------
| Activation
|--------------------------------------------------------------------------
*/

register_activation_hook(__FILE__, 'gpstracker_on_activate');

function gpstracker_on_activate()
{
    if (function_exists('gpstracker_create_table')) {
        gpstracker_create_table();
    }

    if (!get_option('gpstracker_api_key')) {
        update_option(
            'gpstracker_api_key',
            wp_generate_password(40, false, false)
        );
    }

    add_option('gpstracker_provider', 'osm');
    add_option('gpstracker_maptiler_style', 'streets');
    add_option('gpstracker_maptiler_key', '');
    add_option('gpstracker_debug_log', '0');
    add_option('gpstracker_debug_level', 'INFO');
}

/*
|--------------------------------------------------------------------------
| Admin menu
|--------------------------------------------------------------------------
*/

add_action('admin_menu', function () {
    add_menu_page(
        GPSTRACKER_NAME,
        GPSTRACKER_NAME,
        'manage_options',
        'gps-tracker-admin',
        'render_gps_tracker_admin',
        'dashicons-location-alt',
        100
    );
});
