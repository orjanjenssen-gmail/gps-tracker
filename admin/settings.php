<?php
/**
 * Filename: settings.php
 * Description: Admin settings page for GPS Tracker. Handles configuration for
 *              REST API access, map providers (including MapTiler),
 *              HTTP Basic Authentication for OwnTracks, and debug logging.
 * Version: 1.6
 * Author: Ørjan Jenssen
 */

defined('ABSPATH') or exit;

/*
|--------------------------------------------------------------------------
| SAVE SETTINGS
|--------------------------------------------------------------------------
*/

// Debug logging
if (isset($_POST['save_debug']) && check_admin_referer('gpstracker_debug')) {
    update_option(
        'gpstracker_debug_log',
        isset($_POST['gpstracker_debug_log']) ? '1' : '0'
    );
    echo '<div class="updated"><p>Debug setting saved.</p></div>';
}

// Regenerate REST API key
if (isset($_POST['regen_api_key']) && check_admin_referer('gpstracker_api_key')) {
    update_option(
        'gps_tracker_api_key',
        wp_generate_password(40, false, false)
    );
    echo '<div class="updated"><p>New API key generated.</p></div>';
}

// Save HTTP Basic Auth credentials
if (isset($_POST['save_http_auth']) && check_admin_referer('gpstracker_http_auth')) {
    update_option(
        'gpstracker_http_username',
        sanitize_text_field($_POST['gpstracker_http_username'] ?? '')
    );
    update_option(
        'gpstracker_http_password',
        sanitize_text_field($_POST['gpstracker_http_password'] ?? '')
    );
    echo '<div class="updated"><p>Username and password saved.</p></div>';
}

// Map settings
if (isset($_POST['save_map_settings']) && check_admin_referer('gpstracker_map_settings')) {

    update_option(
        'gpstracker_provider',
        sanitize_text_field($_POST['gpstracker_provider'] ?? 'osm')
    );

    update_option(
        'gpstracker_maptiler_style',
        sanitize_text_field($_POST['gpstracker_maptiler_style'] ?? 'streets')
    );

    update_option(
        'gpstracker_maptiler_key',
        sanitize_text_field($_POST['gpstracker_maptiler_key'] ?? '')
    );

    echo '<div class="updated"><p>Map settings saved.</p></div>';
}

/*
|--------------------------------------------------------------------------
| LOAD CURRENT VALUES
|--------------------------------------------------------------------------
*/

$debug     = get_option('gpstracker_debug_log', '0');
$api_key   = get_option('gps_tracker_api_key', '');
$http_user = get_option('gpstracker_http_username', '');
$http_pass = get_option('gpstracker_http_password', '');

$provider  = get_option('gpstracker_provider', 'osm');
$style     = get_option('gpstracker_maptiler_style', 'streets');
$map_key   = get_option('gpstracker_maptiler_key', '');

?>

<h2>🔐 REST API</h2>

<form method="post">
    <?php wp_nonce_field('gpstracker_api_key'); ?>
    <p>
        <label>API key (for external clients)</label><br>
        <input type="text"
               readonly
               style="width:520px;font-family:monospace"
               value="<?php echo esc_attr($api_key); ?>">
    </p>
    <p>
        <input type="submit"
               name="regen_api_key"
               class="button"
               value="Regenerate API key">
    </p>
</form>

<hr>

<h2>🗺 Map</h2>

<form method="post">
    <?php wp_nonce_field('gpstracker_map_settings'); ?>

    <p>
        <label>Provider</label><br>
        <select name="gpstracker_provider">
            <option value="osm" <?php selected($provider, 'osm'); ?>>OpenStreetMap</option>
            <option value="maptiler" <?php selected($provider, 'maptiler'); ?>>MapTiler</option>
            <option value="esri" <?php selected($provider, 'esri'); ?>>Esri Satellite</option>
            <option value="topo" <?php selected($provider, 'topo'); ?>>OpenTopoMap</option>
        </select>
    </p>

    <p>
        <label>MapTiler style</label><br>
        <select name="gpstracker_maptiler_style">
            <?php
            foreach (['streets','basic','bright','pastel','hybrid','satellite','aquarelle'] as $s) {
                echo '<option value="' . esc_attr($s) . '" ' .
                     selected($style, $s, false) . '>' .
                     esc_html($s) .
                     '</option>';
            }
            ?>
        </select>
    </p>

    <p>
        <label>MapTiler API key</label><br>
        <input type="password"
               name="gpstracker_maptiler_key"
               value="<?php echo esc_attr($map_key); ?>"
               style="width:420px;font-family:monospace">
        <br>
        <small>Used only when MapTiler is selected as provider.</small>
    </p>

    <p>
        <input type="submit"
               name="save_map_settings"
               class="button button-primary"
               value="Save map settings">
    </p>
</form>

<hr>

<h2>📡 OwnTracks – HTTP Basic Auth</h2>

<form method="post">
    <?php wp_nonce_field('gpstracker_http_auth'); ?>

    <p>
        <label>Username</label><br>
        <input type="text"
               name="gpstracker_http_username"
               value="<?php echo esc_attr($http_user); ?>"
               style="width:300px">
    </p>

    <p>
        <label>Password</label><br>
        <input type="password"
               name="gpstracker_http_password"
               value="<?php echo esc_attr($http_pass); ?>"
               style="width:300px">
    </p>

    <p>
        <input type="submit"
               name="save_http_auth"
               class="button"
               value="Save username / password">
    </p>
</form>

<hr>

<h2>🛠 Debug</h2>

<form method="post">
    <?php wp_nonce_field('gpstracker_debug'); ?>
    <label>
        <input type="checkbox"
               name="gpstracker_debug_log"
               value="1"
               <?php checked($debug, '1'); ?>>
        Enable debug logging to file
    </label>

    <p>
        <input type="submit"
               name="save_debug"
               class="button"
               value="Save">
    </p>
</form>
