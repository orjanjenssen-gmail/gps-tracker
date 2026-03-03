<?php
/**
 * Filename: debug.php
 * Description: Live debug log viewer for GPS Tracker.
 * Version: 1.8
 * Author: Ørjan Jenssen
 */

defined('ABSPATH') or exit;
?>

<h2>🪵 Debug</h2>

<div id="debugBox" style="
    background:#111;
    color:#0f0;
    padding:10px;
    font-family:monospace;
    font-size:12px;
    max-height:500px;
    overflow:auto;
    border:1px solid #333;
"></div>

<script>
const gpstrackerRestNonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
</script>

<script>
async function loadDebug() {
    try {
        const res = await fetch('<?php echo esc_url(rest_url('gpstracker/v1/debug-log')); ?>', {
    credentials: 'same-origin',
    headers: {
        'X-WP-Nonce': gpstrackerRestNonce
    }
});
        if (!res.ok) {
            throw new Error('HTTP ' + res.status);
        }

        const data = await res.json();

        const box = document.getElementById('debugBox');
        box.innerHTML = '';

        if (!data.lines || data.lines.length === 0) {
            box.innerHTML = '<em>No debug entries.</em>';
            return;
        }

        data.lines.forEach(line => {
            const div = document.createElement('div');
            div.textContent = line;
            box.appendChild(div);
        });

    } catch (err) {
        document.getElementById('debugBox').innerHTML =
            '<span style="color:red;">Failed to load debug log</span>';
        console.error(err);
    }
}

loadDebug();
setInterval(loadDebug, 5000);
</script>
