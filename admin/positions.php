<?php
/**
 * Filename: positions.php
 * Description: Admin view listing recent GPS positions with correct local time.
 * Version: 1.7
 * Author: Ørjan Jenssen
 */

defined('ABSPATH') or exit;

global $wpdb;
$table = $wpdb->prefix . 'gps_tracker';

// Delete single row
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $wpdb->delete($table, ['id' => (int) $_GET['delete']]);
    echo '<div class="updated"><p>Position deleted.</p></div>';
}

// Delete all
if (isset($_POST['delete_all']) && check_admin_referer('gpstracker_delete_all')) {
    $wpdb->query("TRUNCATE TABLE $table");
    echo '<div class="updated"><p>All positions deleted.</p></div>';
}

echo '<form method="post">';
wp_nonce_field('gpstracker_delete_all');
echo '<p><input type="submit" name="delete_all" class="button button-danger" value="Delete all" onclick="return confirm(\'Are you sure?\')"></p>';
echo '</form>';

$rows = $wpdb->get_results("SELECT * FROM $table ORDER BY timestamp DESC LIMIT 100");

if (!$rows) {
    echo '<p>No positions found.</p>';
    return;
}

echo '<table class="widefat striped">';
echo '<thead>
<tr>
    <th>ID</th>
    <th>Time</th>
    <th>Lat</th>
    <th>Lon</th>
    <th>Altitude</th>
    <th>Speed</th>
    <th>Battery</th>
    <th></th>
</tr>
</thead><tbody>';

foreach ($rows as $r) {
    echo '<tr>';
    echo '<td>' . esc_html($r->id) . '</td>';
    echo '<td>' . esc_html(gpstracker_format_datetime($r->timestamp, 'H:i d.m.Y')) . '</td>';
    echo '<td>' . esc_html($r->lat) . '</td>';
    echo '<td>' . esc_html($r->lon) . '</td>';
    echo '<td>' . esc_html($r->altitude ?? '-') . ' m</td>';
    echo '<td>' . esc_html($r->speed ?? '-') . ' km/h</td>';
    echo '<td>' . esc_html($r->battery ?? '-') . '%</td>';
    echo '<td><a class="button" href="?page=gps-tracker-admin&tab=positions&delete=' . (int) $r->id . '">Delete</a></td>';
    echo '</tr>';
}

echo '</tbody></table>';
