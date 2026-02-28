<?php
/**
 * Filename: database.php
 * Description: Database schema definition and migration logic for GPS Tracker.
 *              Creates and updates the custom table used to store GPS locations,
 *              including coordinates, altitude, speed, battery level, timestamp
 *              and device identifier (TID).
 * Version: 1.6
 * Author: Ørjan Jenssen
 */

defined('ABSPATH') or exit;

/**
 * Create or update the GPS Tracker database table
 *
 * This function is safe to run multiple times.
 * It uses dbDelta() to apply schema changes incrementally.
 */
function gpstracker_create_table()
{
    global $wpdb;

    $table = $wpdb->prefix . 'gps_tracker';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        lat DOUBLE NOT NULL,
        lon DOUBLE NOT NULL,
        altitude DOUBLE DEFAULT NULL,
        speed DOUBLE DEFAULT NULL,
        timestamp DATETIME NOT NULL,
        tid VARCHAR(10) DEFAULT NULL,
        battery INT DEFAULT NULL,

        PRIMARY KEY (id),
        KEY idx_timestamp (timestamp),
        KEY idx_lat_lon (lat, lon),
        KEY idx_tid (tid)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
