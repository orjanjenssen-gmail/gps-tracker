<?php
/**
 * Filename: statistics.php
 * Description: Admin statistics dashboard for GPS Tracker.
 *              Displays aggregated GPS data including total points,
 *              first/last timestamps, points per day, battery levels over time,
 *              and points per tracking device (TID).
 * Version: 1.6
 * Author: Ørjan Jenssen
 */

defined('ABSPATH') or exit;

global $wpdb;

$table = $wpdb->prefix . 'gps_tracker';

// -----------------------------------------------------------------------------
// OVERVIEW
// -----------------------------------------------------------------------------

$total_points = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
$first_point  = $wpdb->get_var("SELECT MIN(timestamp) FROM $table");
$last_point   = $wpdb->get_var("SELECT MAX(timestamp) FROM $table");

// -----------------------------------------------------------------------------
// POINTS PER DAY (LAST 14 DAYS)
// -----------------------------------------------------------------------------

$points_per_day = $wpdb->get_results("
    SELECT DATE(timestamp) AS day, COUNT(*) AS count
    FROM $table
    GROUP BY DATE(timestamp)
    ORDER BY day ASC
    LIMIT 14
");

// -----------------------------------------------------------------------------
// BATTERY OVER TIME (LAST 20 POINTS)
// -----------------------------------------------------------------------------

$battery_over_time = $wpdb->get_results("
    SELECT timestamp, battery
    FROM $table
    WHERE battery IS NOT NULL
    ORDER BY timestamp DESC
    LIMIT 20
");

// -----------------------------------------------------------------------------
// POINTS PER DEVICE (TID)
// -----------------------------------------------------------------------------

$points_per_tid = $wpdb->get_results("
    SELECT tid, COUNT(*) AS count
    FROM $table
    WHERE tid IS NOT NULL
    GROUP BY tid
    ORDER BY count DESC
");

// -----------------------------------------------------------------------------
// DATA FOR JAVASCRIPT
// -----------------------------------------------------------------------------

$days        = array_column($points_per_day, 'day');
$day_counts = array_column($points_per_day, 'count');

$battery_labels = array_reverse(array_map(fn($r) => $r->timestamp, $battery_over_time));
$battery_values = array_reverse(array_map(fn($r) => (int) $r->battery, $battery_over_time));

$tid_labels = array_map(fn($r) => $r->tid ?: 'Unknown', $points_per_tid);
$tid_counts = array_map(fn($r) => (int) $r->count, $points_per_tid);
?>

<h2>📊 GPS Statistics</h2>

<?php if ($total_points === 0): ?>
    <p><em>No GPS data has been recorded yet.</em></p>
<?php return; endif; ?>

<div class="gpstracker-stats">

    <div class="gpstracker-cards">
        <div class="card">
            <strong><?php echo esc_html($total_points); ?></strong>
            <span>Total points</span>
        </div>
        <div class="card">
            <strong><?php echo esc_html($first_point); ?></strong>
            <span>First recorded</span>
        </div>
        <div class="card">
            <strong><?php echo esc_html($last_point); ?></strong>
            <span>Last recorded</span>
        </div>
    </div>

    <div class="gpstracker-charts">
        <canvas id="pointsPerDay"></canvas>
        <canvas id="batteryChart"></canvas>
        <canvas id="pointsPerTid"></canvas>
    </div>

</div>

<style>
.gpstracker-cards {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.gpstracker-cards .card {
    background: #fff;
    padding: 20px;
    border-left: 5px solid #2271b1;
    box-shadow: 0 1px 2px rgba(0,0,0,.05);
    min-width: 200px;
}

.gpstracker-cards .card strong {
    display: block;
    font-size: 20px;
}

.gpstracker-cards .card span {
    color: #666;
}

.gpstracker-charts {
    display: grid;
    grid-template-columns: 1fr;
    gap: 40px;
    max-width: 1000px;
}

canvas {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const chartDefaults = {
    responsive: true,
    plugins: {
        legend: { display: true },
        tooltip: { enabled: true }
    }
};

// 📈 Points per day
new Chart(document.getElementById('pointsPerDay'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($days); ?>,
        datasets: [{
            label: 'Points per day',
            data: <?php echo json_encode($day_counts); ?>,
            borderWidth: 2,
            tension: 0.3,
            fill: true
        }]
    },
    options: chartDefaults
});

// 🔋 Battery over time
new Chart(document.getElementById('batteryChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($battery_labels); ?>,
        datasets: [{
            label: 'Battery %',
            data: <?php echo json_encode($battery_values); ?>,
            borderWidth: 2,
            tension: 0.3
        }]
    },
    options: {
        ...chartDefaults,
        scales: {
            y: {
                min: 0,
                max: 100
            }
        }
    }
});

// 📊 Points per device (TID)
new Chart(document.getElementById('pointsPerTid'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($tid_labels); ?>,
        datasets: [{
            label: 'Points per device',
            data: <?php echo json_encode($tid_counts); ?>
        }]
    },
    options: chartDefaults
});
</script>
