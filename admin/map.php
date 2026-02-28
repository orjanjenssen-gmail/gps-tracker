<?php
/**
 * Filename: map.php
 * Description: Admin map view with GPS track polyline.
 * Version: 1.7
 * Author: Ørjan Jenssen
 */

defined('ABSPATH') or exit;
?>

<h2>🗺 Map (with track)</h2>

<div id="gps-admin-map" style="height:600px;width:100%;border:1px solid #ccc;"></div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
(async function () {

    const map = L.map('gps-admin-map');
    const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
    tiles.addTo(map);

    const historyRes = await fetch('<?php echo esc_url(rest_url('gpstracker/v1/history')); ?>');
    const points = await historyRes.json();

    if (!points.length) return;

    const polyline = L.polyline(points, { color: 'red' }).addTo(map);
    map.fitBounds(polyline.getBounds());

    L.marker(points[points.length - 1]).addTo(map)
        .bindPopup('Latest position')
        .openPopup();

})();
</script>
