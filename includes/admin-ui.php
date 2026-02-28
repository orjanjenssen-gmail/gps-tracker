<?php
/**
 * Filename: admin-ui.php
 * Description: Admin interface and navigation for GPS Tracker.
 * Version: 1.7
 * Author: Ørjan Jenssen
 */

defined('ABSPATH') or exit;

function render_gps_tracker_admin()
{
    $tab = $_GET['tab'] ?? 'positions';

    echo '<div class="wrap">';
    echo '<h1>' . esc_html(GPSTRACKER_NAME) . '</h1>';

    echo '<h2 class="nav-tab-wrapper">';
    gps_tab('positions', 'Posisjoner', $tab);
    gps_tab('map', '🗺 Kart', $tab);
    gps_tab('statistics', '📊 Statistikk', $tab);
    gps_tab('settings', 'Innstillinger', $tab);
    gps_tab('debug', '🪵 Debug', $tab);
    echo '</h2>';

    $file = plugin_dir_path(__FILE__) . '../admin/' . basename($tab) . '.php';

    if (file_exists($file)) {
        require $file;
    } else {
        echo '<p>Invalid tab.</p>';
    }

    // Footer (ONLY for GPS Tracker admin pages)
    echo '<hr style="margin-top:40px;">';
    echo '<p style="color:#666;font-size:12px;">';
    echo esc_html(GPSTRACKER_NAME) . ' v' . esc_html(GPSTRACKER_VERSION);
    echo ' • Admin module';
    echo '</p>';

    echo '</div>';
}

function gps_tab($slug, $label, $active)
{
    $class = ($slug === $active)
        ? 'nav-tab nav-tab-active'
        : 'nav-tab';

    echo '<a class="' . esc_attr($class) . '" href="?page=gps-tracker-admin&tab=' . esc_attr($slug) . '">';
    echo esc_html($label);
    echo '</a>';
}
