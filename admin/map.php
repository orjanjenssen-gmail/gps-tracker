<?php
/**
 * Filename: map.php
 * Description: Admin map view with GPS track polyline.
 * Version: 1.8
 * Author: Ørjan Jenssen
 */

defined('ABSPATH') or exit;

$provider = get_option('gpstracker_provider', 'osm');
$style    = get_option('gpstracker_maptiler_style', 'streets');
$map_key  = get_option('gpstracker_maptiler_key', '');
?>

<h2>🗺 Map (with track)</h2>

<div id="gps-admin-map" style="height:600px;width:100%;border:1px solid #ccc;"></div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
const gpstrackerRestNonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
</script>

<script>
(async function () {

    const map = L.map('gps-admin-map');
    let tiles;

    <?php if ($provider === 'maptiler' && !empty($map_key)) : ?>
        tiles = L.tileLayer(
            'https://api.maptiler.com/maps/<?php echo esc_js($style); ?>/256/{z}/{x}/{y}.png?key=<?php echo esc_js($map_key); ?>',
            { attribution: '&copy; MapTiler' }
        );
    <?php elseif ($provider === 'esri') : ?>
        tiles = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            { attribution: '&copy; Esri' }
        );
    <?php elseif ($provider === 'topo') : ?>
        tiles = L.tileLayer(
            'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
            { attribution: '&copy; OpenTopoMap' }
        );
    <?php else : ?>
        tiles = L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            { attribution: '&copy; OpenStreetMap' }
        );
    <?php endif; ?>

    tiles.addTo(map);

    const historyRes = await fetch('<?php echo esc_url(rest_url('gpstracker/v1/history')); ?>', {
        credentials: 'same-origin',
        headers: { 'X-WP-Nonce': gpstrackerRestNonce }
    });

    if (!historyRes.ok) {
        console.error('History fetch failed:', historyRes.status);
        return;
    }

    const points = await historyRes.json();
    if (!points.length) return;

    const latlngs = points.map(p => [p.lat, p.lon]);

    const polyline = L.polyline(latlngs, { color: 'red' }).addTo(map);
    map.fitBounds(polyline.getBounds());

    const last = points[points.length - 1];

    L.marker([last.lat, last.lon]).addTo(map)
        .bindPopup(`
            <strong>Latest position</strong><br>
            Date: ${last.date}<br>
            Time: ${last.time}<br>
            Altitude: ${last.altitude ?? '-'} m<br>
            Speed: ${last.speed ?? '-'} km/h<br>
            Battery: ${last.battery ?? '-'} %
        `)
        .openPopup();

})();
</script>
