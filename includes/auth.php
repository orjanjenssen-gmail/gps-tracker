<?php
/**
 * Filename: auth.php
 * Description: Admin interface and navigation for GPS Tracker.
 * Version: 1.7
 * Author: Ørjan Jenssen
 */
defined('ABSPATH') || exit;

/**
 * Authenticate incoming REST request
 *
 * @return array|WP_Error
 */
function gps_authenticate_request(WP_REST_Request $request) {

    // API key auth
    $api_key = $request->get_header('x-gps-api-key');
    if ($api_key) {
        $stored_key = get_option('gps_api_key');

        if (!$stored_key || !hash_equals((string)$stored_key, (string)$api_key)) {
            gps_log('WARN', 'Invalid API key');
            return new WP_Error('gps_auth_failed', 'Invalid API key', ['status' => 401]);
        }

        return [
            'type' => 'api_key',
            'id'   => 'api-client',
        ];
    }

    // HTTP Basic Auth (OwnTracks)
    if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
        return new WP_Error('gps_auth_required', 'Authentication required', ['status' => 401]);
    }

    $user = sanitize_text_field($_SERVER['PHP_AUTH_USER']);
    $pass = $_SERVER['PHP_AUTH_PW'];

    $stored_user = get_option('gps_http_user');
    $stored_pass = get_option('gps_http_pass');

    if ($user !== $stored_user || $pass !== $stored_pass) {
        gps_log('WARN', 'Invalid HTTP auth');
        return new WP_Error('gps_auth_failed', 'Invalid credentials', ['status' => 401]);
    }

    return [
        'type' => 'owntracks',
        'id'   => $user,
    ];
}
