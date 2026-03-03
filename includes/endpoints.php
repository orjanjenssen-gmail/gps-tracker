<?php
/**
 * Filename: endpoints.php
 * Description: REST API endpoints for GPS Tracker.
 * Version: 1.8
 * Author: Ørjan Jenssen
 */

defined('ABSPATH') or exit;

add_action('rest_api_init', function () {

    register_rest_route('gpstracker/v1', '/location', [
        'methods'  => 'POST',
        'callback' => 'gpstracker_receive_location',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('gpstracker/v1', '/current', [
        'methods'  => 'GET',
        'callback' => 'gpstracker_get_current_location',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('gpstracker/v1', '/history', [
        'methods'  => 'GET',
        'callback' => 'gpstracker_get_history',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ]);

    register_rest_route('gpstracker/v1', '/debug-log', [
        'methods'  => 'GET',
        'callback' => 'gpstracker_get_debug_log',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ]);
});

/*
|--------------------------------------------------------------------------
| Receive GPS data
|--------------------------------------------------------------------------
*/

function gpstracker_receive_location(WP_REST_Request $request)
{
    global $wpdb;

    $data = json_decode($request->get_body(), true);

    if (!$data || ($data['_type'] ?? '') !== 'location') {
        return ['status' => 'ignored'];
    }

    if (!isset($data['lat'], $data['lon'])) {
        return ['status' => 'error'];
    }

    $speed = isset($data['vel'])
        ? round(((float)$data['vel']) * 3.6, 1)
        : null;

    $wpdb->insert(
        $wpdb->prefix . 'gps_tracker',
        [
            'lat'       => (float) $data['lat'],
            'lon'       => (float) $data['lon'],
            'altitude'  => $data['alt'] ?? null,
            'speed'     => $speed,
            'battery'   => $data['batt'] ?? null,
            'tid'       => $data['tid'] ?? null,
            'timestamp' => isset($data['tst'])
                ? gmdate('Y-m-d H:i:s', (int) $data['tst'])
                : current_time('mysql', true),
        ]
    );

    return ['status' => 'ok'];
}

/*
|--------------------------------------------------------------------------
| Latest position
|--------------------------------------------------------------------------
*/

function gpstracker_get_current_location()
{
    global $wpdb;

    $row = $wpdb->get_row("
        SELECT lat, lon, altitude, speed, battery, timestamp
        FROM {$wpdb->prefix}gps_tracker
        ORDER BY timestamp DESC
        LIMIT 1
    ");

    if (!$row) {
        return ['status' => 'no_data'];
    }

    return [
        'lat'          => (float) $row->lat,
        'lon'          => (float) $row->lon,
        'altitude'     => $row->altitude,
        'speed'        => $row->speed,
        'battery'      => $row->battery,
        'updated_time' => gpstracker_format_datetime($row->timestamp, 'H:i'),
        'updated_date' => gpstracker_format_datetime($row->timestamp, 'd-m-Y'),
        'timestamp_utc'=> $row->timestamp,
    ];
}

/*
|--------------------------------------------------------------------------
| History for admin map
|--------------------------------------------------------------------------
*/

function gpstracker_get_history()
{
    global $wpdb;

    $rows = $wpdb->get_results("
        SELECT lat, lon, altitude, speed, battery, timestamp
        FROM {$wpdb->prefix}gps_tracker
        ORDER BY timestamp ASC
        LIMIT 500
    ");

    return array_map(function ($r) {

        return [
            'lat'      => (float) $r->lat,
            'lon'      => (float) $r->lon,
            'altitude' => $r->altitude,
            'speed'    => $r->speed,
            'battery'  => $r->battery,
            'date'     => gpstracker_format_datetime($r->timestamp, 'd-m-Y'),
            'time'     => gpstracker_format_datetime($r->timestamp, 'H:i')
        ];

    }, $rows);
}

/*
|--------------------------------------------------------------------------
| Debug log REST endpoint
|--------------------------------------------------------------------------
*/

function gpstracker_get_debug_log()
{
    $file = gpstracker_get_log_file();

    if (!file_exists($file) || !is_readable($file)) {
        return ['lines' => []];
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    return [
        'lines' => array_slice(array_reverse($lines), 0, 300)
    ];
}
