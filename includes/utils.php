<?php
/**
 * Filename: utils.php
 * Description: Utility helpers for GPS Tracker.
 *              Provides logging helpers and a unified timezone formatter
 *              to ensure consistent date/time handling across the plugin.
 * Version: 1.7
 * Author: Ørjan Jenssen
 */

defined('ABSPATH') or exit;

/*
|--------------------------------------------------------------------------
| Time & Date helpers
|--------------------------------------------------------------------------
*/

/**
 * Format UTC datetime string into site local timezone
 *
 * @param string $utc_datetime MySQL DATETIME in UTC
 * @param string $format       PHP date format
 * @return string
 */
function gpstracker_format_datetime($utc_datetime, $format = 'Y-m-d H:i:s')
{
    if (!$utc_datetime) {
        return '';
    }

    try {
        $tz = wp_timezone();
        $dt = new DateTime($utc_datetime, new DateTimeZone('UTC'));
        $dt->setTimezone($tz);
        return $dt->format($format);
    } catch (Exception $e) {
        return $utc_datetime;
    }
}

/*
|--------------------------------------------------------------------------
| Logging helpers
|--------------------------------------------------------------------------
*/

function gpstracker_get_log_dir()
{
    return plugin_dir_path(__DIR__) . 'logs/';
}

function gpstracker_get_log_file()
{
    return gpstracker_get_log_dir() . 'GPSlogger_debug.txt';
}

function gpstracker_ensure_log_directory()
{
    $dir = gpstracker_get_log_dir();
    if (!file_exists($dir)) {
        @mkdir($dir, 0755, true);
    }
    return is_dir($dir) && is_writable($dir);
}

function gpstracker_log_internal($level, $message)
{
    if (!filter_var(get_option('gpstracker_debug_log', false), FILTER_VALIDATE_BOOLEAN)) {
        return;
    }

    $allowed = get_option('gpstracker_debug_level', 'INFO');
    $levels  = ['INFO' => 1, 'WARN' => 2, 'ERROR' => 3];

    if (!isset($levels[$level], $levels[$allowed])) {
        return;
    }

    if ($levels[$level] < $levels[$allowed]) {
        return;
    }

    if (!gpstracker_ensure_log_directory()) {
        return;
    }

    $time = current_time('Y-m-d H:i:s');
    $line = "[$time] [$level] $message" . PHP_EOL;
    file_put_contents(gpstracker_get_log_file(), $line, FILE_APPEND | LOCK_EX);
}

function gpstracker_log_info($m)  { gpstracker_log_internal('INFO',  $m); }
function gpstracker_log_warn($m)  { gpstracker_log_internal('WARN',  $m); }
function gpstracker_log_error($m) { gpstracker_log_internal('ERROR', $m); }
