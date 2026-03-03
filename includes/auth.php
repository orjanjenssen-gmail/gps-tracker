<?php
/**
 * Filename: auth.php
 * Description: Admin interface and navigation for GPS Tracker.
 * Version: 1.7
 * Author: Ørjan Jenssen
 */
defined('ABSPATH') || exit;
function gpstracker_authenticate_request(WP_REST_Request $request)
{
    // API Key authentication
    $api_key = $request->get_header('x-gps-api-key');

    if ($api_key) {
        $stored_key = get_option('gpstracker_api_key');

        if (!$stored_key || !hash_equals((string)$stored_key, (string)$api_key)) {
            gpstracker_log_warn('Invalid API key');
            return new WP_Error('gps_auth_failed', 'Invalid API key', ['status' => 401]);
        }

        return true;
    }

    // HTTP Basic (OwnTracks)
    if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
        return new WP_Error('gps_auth_required', 'Authentication required', ['status' => 401]);
    }

    $user = sanitize_text_field($_SERVER['PHP_AUTH_USER']);
    $pass = $_SERVER['PHP_AUTH_PW'];

    $stored_user = get_option('gpstracker_http_username');
    $stored_pass = get_option('gpstracker_http_password');

    if (!$stored_user || !$stored_pass || $user !== $stored_user || $pass !== $stored_pass) {
        gpstracker_log_warn('Invalid HTTP auth');
        return new WP_Error('gps_auth_failed', 'Invalid credentials', ['status' => 401]);
    }

    return true;
}
