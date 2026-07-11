@props(['markers' => [], 'class' => 'h-[68vh] min-h-[420px]'])

{{--
    Reusable Leaflet map of waypoints. Works both on plain Blade pages (public site)
    and inside Livewire components (wire:ignore keeps Livewire from patching the map).
    Expects $markers: array of { name, x, y, z, dimension, color, note, shot }.
    Leaflet must be loaded on the page (public layout loads it; authed pages use @assets).
--}}
<div
    data-waypoint-map
    wire:ignore
    {{ $attributes->merge(['class' => "w-full rounded-xl border border-zinc-200 dark:border-zinc-700 {$class}"]) }}
    style="background: #0c0f12;"
    data-markers="{{ json_encode($markers) }}"
></div>

<script>
    (function () {
        function esc(value) {
            return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function initWaypointMaps() {
            if (typeof L === 'undefined') {
                return window.setTimeout(initWaypointMaps, 100);
            }

            document.querySelectorAll('[data-waypoint-map]:not([data-inited])').forEach(function (el) {
                el.setAttribute('data-inited', '1');

                var waypoints = JSON.parse(el.dataset.markers || '[]');
                var map = L.map(el, { crs: L.CRS.Simple, minZoom: -8, maxZoom: 4 });

                // Minecraft coords -> Leaflet LatLng: north (-Z) is up, so lat = -z, lng = x.
                var pts = waypoints.map(function (w) { return [-w.z, w.x]; });

                waypoints.forEach(function (w) {
                    var marker = L.circleMarker([-w.z, w.x], {
                        radius: 8, color: '#0c0f12', weight: 2, fillColor: w.color, fillOpacity: 1,
                    }).addTo(map);

                    var html = '<div style="font-family: sans-serif;">';
                    if (w.shot) { html += '<img src="' + esc(w.shot) + '" style="width:200px;border-radius:6px;display:block;margin-bottom:6px;">'; }
                    html += '<strong>' + esc(w.name) + '</strong><br>';
                    html += '<span style="font-family: monospace; color:#333;">' + w.x + ', ' + w.y + ', ' + w.z + '</span> · ' + esc(w.dimension);
                    if (w.note) { html += '<br><span>' + esc(w.note) + '</span>'; }
                    html += '</div>';

                    marker.bindPopup(html);
                    marker.bindTooltip(esc(w.name), { direction: 'top', offset: [0, -8] });
                });

                if (pts.length) {
                    map.fitBounds(L.latLngBounds(pts).pad(0.5));
                } else {
                    map.setView([0, 0], 0);
                }
            });
        }

        initWaypointMaps();
        document.addEventListener('livewire:navigated', initWaypointMaps);
        window.addEventListener('load', initWaypointMaps);
    })();
</script>
