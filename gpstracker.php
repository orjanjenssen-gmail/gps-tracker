<?php
/**
 * Plugin Name: GPSTracker
 * Description: Receives GPS data via REST API and displays positions on maps.
 * Version: 1.8
 * Author: Ørjan Jenssen
 * Author URI: https://www.tulipankroken.no
 * Plugin URI: https://github.com/orjanjenssen-gmail/gps-tracker/
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
require_once plugin_dir_path(__FILE__) . 'includes/auth.php'; // <-- Kun lagt til
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

    if (!get_option('gps_tracker_api_key')) {
        update_option(
            'gps_tracker_api_key',
            wp_generate_password(40, false, false)
        );
    }

    add_option('gpstracker_provider', 'osm');
    add_option('gpstracker_maptiler_style', 'streets');
    add_option('gpstracker_maptiler_key', '');
    add_option('gpstracker_debug_log', '0');
    add_option('gpstracker_debug_level', 'INFO');
    add_option('gpstracker_db_version', GPSTRACKER_VERSION);
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

/*
|--------------------------------------------------------------------------
| Shortcode: [gps_tracker_map]
|--------------------------------------------------------------------------
*/

function gps_tracker_map_shortcode()
{
    ob_start();
    ?>
    <div id="gps-tracker-map"></div>

    <style>
    #gps-tracker-map {
        width: 100%;
        height: 600px !important;
        min-height: 600px !important;
    }
    </style>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
    (async function () {

        let map, marker, tiles;

        async function initMap() {
            try {
                const res = await fetch('<?php echo esc_url(rest_url('gpstracker/v1/current')); ?>');
                const data = await res.json();

                if (!data.lat || !data.lon) {
                    document.getElementById('gps-tracker-map').innerHTML =
                        '<p style="color:red;">No GPS data available.</p>';
                    return;
                }

                map = L.map('gps-tracker-map', {
                    zoomControl: true,
                    attributionControl: true
                }).setView([data.lat, data.lon], 15);

                tiles = L.tileLayer(
                    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                    { attribution: '&copy; OpenStreetMap' }
                ).addTo(map);

                marker = L.marker([data.lat, data.lon]).addTo(map);

                updatePopup(data);

                setTimeout(() => {
                    map.invalidateSize();
                }, 300);

                setInterval(updatePosition, 60000);

            } catch (err) {
                console.error(err);
                document.getElementById('gps-tracker-map').innerHTML =
                    '<p style="color:red;">Failed to load map.</p>';
            }
        }

        async function updatePosition() {
            const res = await fetch('<?php echo esc_url(rest_url('gpstracker/v1/current')); ?>');
            const data = await res.json();

            if (!data.lat || !data.lon) return;

            marker.setLatLng([data.lat, data.lon]);
            map.panTo([data.lat, data.lon], { animate: true });
            updatePopup(data);
        }

        function updatePopup(data) {
            marker.bindPopup(`
                <strong>Latest position</strong><br>
                Date: ${data.updated_date}<br>
                Time: ${data.updated_time}<br>
                Altitude: ${data.altitude ?? '-'} m<br>
                Speed: ${data.speed ?? '-'} km/h<br>
                Battery: ${data.battery ?? '-'} %
            `);
        }

        initMap();

    })();
    </script>
    <?php

    return ob_get_clean();
}

add_shortcode('gps_tracker_map', 'gps_tracker_map_shortcode');
