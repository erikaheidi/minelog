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
    style="background-color: #e8d9b5; background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='p'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix type='matrix' values='0 0 0 0 0.42  0 0 0 0 0.30  0 0 0 0 0.15  0 0 0 0.06 0'/%3E%3C/filter%3E%3Crect width='160' height='160' filter='url(%23p)'/%3E%3C/svg%3E&quot;); box-shadow: inset 0 0 90px rgba(70,45,20,0.35);"
    data-markers="{{ json_encode($markers) }}"
></div>

<script>
    (function () {
        function esc(value) {
            return String(value === null || value === undefined ? '' : value).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        // Map zoom range. Large worlds fit at negative zoom, so the grid layer must be
        // told to render there too (L.GridLayer defaults to minZoom: 0 and would draw nothing).
        var MIN_ZOOM = -8;
        var MAX_ZOOM = 4;

        // Nearest "nice" world-coordinate spacing so grid lines land ~64-128px apart.
        var GRID_STEPS = [10, 25, 50, 100, 250, 500, 1000, 2500, 5000, 10000, 25000];
        function niceStep(worldPerPixel) {
            var target = worldPerPixel * 96; // aim for ~96px between minor lines
            for (var i = 0; i < GRID_STEPS.length; i++) {
                if (GRID_STEPS[i] >= target) { return GRID_STEPS[i]; }
            }
            return GRID_STEPS[GRID_STEPS.length - 1];
        }

        // Coordinate grid drawn as a Leaflet GridLayer so it pans/zooms with the map.
        // Coord mapping matches the markers: lat = -z, lng = x  (so world X = lng, world Z = -lat).
        function makeGridLayer() {
            var Grid = L.GridLayer.extend({
                createTile: function (coords) {
                    var size = this.getTileSize();
                    var tile = document.createElement('canvas');
                    tile.width = size.x;
                    tile.height = size.y;
                    var ctx = tile.getContext('2d');
                    var map = this._map;

                    // World-coordinate bounds of this tile.
                    var nw = map.unproject(coords.scaleBy(size), coords.z);
                    var se = map.unproject(coords.add([1, 1]).scaleBy(size), coords.z);
                    var minX = nw.lng, maxX = se.lng;          // lng = world X
                    var minZ = -nw.lat, maxZ = -se.lat;        // world Z = -lat (nw.lat >= se.lat)

                    var worldPerPixel = (maxX - minX) / size.x;
                    var step = niceStep(worldPerPixel);
                    var majorEvery = 5;

                    function projX(worldX) {
                        var p = map.project([0, worldX], coords.z);
                        return p.x - coords.x * size.x;
                    }
                    function projZ(worldZ) {
                        var p = map.project([-worldZ, 0], coords.z);
                        return p.y - coords.y * size.y;
                    }

                    ctx.font = '10px monospace';
                    ctx.textBaseline = 'top';

                    // Vertical lines (constant world X).
                    var startX = Math.ceil(minX / step) * step;
                    for (var wx = startX; wx <= maxX; wx += step) {
                        var px = projX(wx);
                        var isAxis = (wx === 0);
                        var isMajor = (Math.round(wx / step) % majorEvery === 0);
                        ctx.beginPath();
                        ctx.moveTo(px + 0.5, 0);
                        ctx.lineTo(px + 0.5, size.y);
                        ctx.lineWidth = isAxis ? 1.5 : 1;
                        ctx.strokeStyle = isAxis ? 'rgba(70,45,20,0.75)'
                            : isMajor ? 'rgba(90,60,30,0.4)' : 'rgba(90,60,30,0.18)';
                        ctx.stroke();
                        if (isMajor || isAxis) {
                            ctx.fillStyle = 'rgba(70,45,20,0.7)';
                            ctx.fillText('x ' + wx, px + 3, 3);
                        }
                    }

                    // Horizontal lines (constant world Z).
                    var startZ = Math.ceil(minZ / step) * step;
                    for (var wz = startZ; wz <= maxZ; wz += step) {
                        var py = projZ(wz);
                        var isAxisZ = (wz === 0);
                        var isMajorZ = (Math.round(wz / step) % majorEvery === 0);
                        ctx.beginPath();
                        ctx.moveTo(0, py + 0.5);
                        ctx.lineTo(size.x, py + 0.5);
                        ctx.lineWidth = isAxisZ ? 1.5 : 1;
                        ctx.strokeStyle = isAxisZ ? 'rgba(70,45,20,0.75)'
                            : isMajorZ ? 'rgba(90,60,30,0.4)' : 'rgba(90,60,30,0.18)';
                        ctx.stroke();
                        if (isMajorZ || isAxisZ) {
                            ctx.fillStyle = 'rgba(70,45,20,0.7)';
                            ctx.fillText('z ' + wz, 3, py + 3);
                        }
                    }

                    return tile;
                },
            });
            return new Grid({ minZoom: MIN_ZOOM, maxZoom: MAX_ZOOM });
        }

        function initWaypointMaps() {
            if (typeof L === 'undefined') {
                return window.setTimeout(initWaypointMaps, 100);
            }

            document.querySelectorAll('[data-waypoint-map]:not([data-inited])').forEach(function (el) {
                el.setAttribute('data-inited', '1');

                var waypoints = JSON.parse(el.dataset.markers || '[]');
                var map = L.map(el, { crs: L.CRS.Simple, minZoom: MIN_ZOOM, maxZoom: MAX_ZOOM });

                // Grid sits under the markers.
                makeGridLayer().addTo(map);

                // Minecraft coords -> Leaflet LatLng: north (-Z) is up, so lat = -z, lng = x.
                var pts = waypoints.map(function (w) { return [-w.z, w.x]; });

                waypoints.forEach(function (w) {
                    var marker = L.circleMarker([-w.z, w.x], {
                        radius: 8, color: '#2b1d0e', weight: 2.5, fillColor: w.color, fillOpacity: 1,
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
