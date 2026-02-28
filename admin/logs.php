<?php
/**
 * Filename: logs.php
 * Description: Admin view for displaying the GPS Tracker debug log file.
 *              Shows the log content in reverse order (newest first).
 * Version: 1.6
 * Author: Ørjan Jenssen
 */

defined('ABSPATH') or exit;

echo '<h2>🪵 Debug log</h2>';

$log_file = gpstracker_get_log_file();

if (!file_exists($log_file)) {
    echo '<p><em>No log file found yet.</em></p>';
    return;
}

if (!is_readable($log_file)) {
    echo '<p style="color:red;">The log file exists but is not readable.</p>';
    return;
}

// Read log file
$lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if (!$lines || count($lines) === 0) {
    echo '<p><em>The log file is empty.</em></p>';
    return;
}

// Show newest entries first
$lines = array_reverse($lines);

echo '<div style="
    background:#111;
    color:#0f0;
    padding:10px;
    font-family: monospace;
    font-size:12px;
    max-height:500px;
    overflow:auto;
    border:1px solid #333;
">';

foreach ($lines as $line) {
    echo esc_html($line) . "<br>";
}

echo '</div>';

// Footer info
echo '<p style="margin-top:10px;color:#666;font-size:12px;">';
echo 'File: <code>' . esc_html($log_file) . '</code><br>';
echo 'Total lines: ' . count($lines);
echo '</p>';
