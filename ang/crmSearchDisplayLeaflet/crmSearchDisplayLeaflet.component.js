(function(angular, $, _) {
  'use strict';

  /**
   * crm-search-display-leaflet component.
   *
   * Renders SearchKit results as an interactive Leaflet map with:
   *  - Custom colour-coded div-icon markers per field value
   *  - MarkerCluster grouping
   *  - Token-based popup content (all display columns)
   *  - Viewport-bound re-query on map move/zoom
   *  - "View contact" link in each popup
   */
  angular.module('crmSearchDisplayLeaflet').component('crmSearchDisplayLeaflet', {

    bindings: {
      apiEntity:  '@',
      search:     '<',
      display:    '<',
      settings:   '<',
      filters:    '<',
      totalCount: '=?',
      afform:     '<?',
    },

    templateUrl: '~/crmSearchDisplayLeaflet/crmSearchDisplayLeaflet.html',

    controller: function($scope, $element, $timeout, crmApi4) {
      var ctrl = this;

      // ── Internal state ────────────────────────────────────────────────────
      var map            = null;
      var markerLayer    = null; // L.markerClusterGroup or L.layerGroup
      var tileLayer      = null;
      var viewportPause  = false; // debounce flag to avoid re-entrancy
      ctrl.loading       = false;
      ctrl.results       = [];
      ctrl.error         = null;

      // ── Defaults ─────────────────────────────────────────────────────────
      var DEFAULT_SETTINGS = {
        latitudeColumn:      'address_primary.geo_code_1',
        longitudeColumn:     'address_primary.geo_code_2',
        labelColumn:         'display_name',
        subtitleColumn:      'address_primary.city',
        markerColorField:    'contact_type',
        colorMap:            { Individual: '#2a6496', Organization: '#c0392b', Household: '#e67e22' },
        enableClustering:    true,
        spiderfyOnMaxZoom:   true,
        showCoverageOnHover: false,
        viewportFilter:      true,
        tileProvider:        'osm',
        tileUrl:             'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        mapHeight:           500,
      };

      // Tile provider URLs
      var TILE_PROVIDERS = {
        osm:          'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        carto_light:  'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
        carto_dark:   'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
        stadia_toner: 'https://tiles.stadiamaps.com/tiles/stamen_toner/{z}/{x}/{y}{r}.png',
      };

      // ── Lifecycle ─────────────────────────────────────────────────────────
      ctrl.$onInit = function() {
        ctrl.currentSettings = angular.merge({}, DEFAULT_SETTINGS, ctrl.settings || {});
        ctrl.currentSettings.tileUrl = TILE_PROVIDERS[ctrl.currentSettings.tileProvider]
          || TILE_PROVIDERS.osm;

        // Wait for DOM to be ready before initialising the map.
        $timeout(function() {
          initMap();
          loadResults();
        }, 0);
      };

      ctrl.$onChanges = function(changes) {
        if (!map) { return; }
        if (changes.settings) {
          ctrl.currentSettings = angular.merge({}, DEFAULT_SETTINGS, ctrl.settings || {});
          ctrl.currentSettings.tileUrl = TILE_PROVIDERS[ctrl.currentSettings.tileProvider]
            || TILE_PROVIDERS.osm;
          updateTileLayer();
        }
        if (changes.filters) {
          loadResults();
        }
      };

      ctrl.$onDestroy = function() {
        if (map) {
          map.remove();
          map = null;
        }
      };

      // ── Map initialisation ────────────────────────────────────────────────
      function initMap() {
        var container = $element.find('.crm-leaflet-map-container')[0];
        if (!container || map) { return; }

        container.style.height = (ctrl.currentSettings.mapHeight || 500) + 'px';

        map = L.map(container, {
          center:    [20, 0],
          zoom:      2,
          zoomControl: false,
        });

        // Custom zoom control (top-right)
        L.control.zoom({ position: 'topright' }).addTo(map);

        // Tile layer
        tileLayer = L.tileLayer(ctrl.currentSettings.tileUrl, {
          attribution: getAttribution(ctrl.currentSettings.tileProvider),
          maxZoom: 19,
        }).addTo(map);

        // Marker layer (cluster or plain)
        markerLayer = buildMarkerLayer();
        markerLayer.addTo(map);

        // Viewport filter: re-query when user pans/zooms.
        if (ctrl.currentSettings.viewportFilter) {
          map.on('moveend', function() {
            if (!viewportPause) {
              loadResults();
            }
          });
        }

        // Invalidate size after Angular digest to handle hidden containers.
        $timeout(function() { map.invalidateSize(); }, 150);
      }

      function buildMarkerLayer() {
        var s = ctrl.currentSettings;
        if (s.enableClustering && window.L && L.markerClusterGroup) {
          return L.markerClusterGroup({
            spiderfyOnMaxZoom:   s.spiderfyOnMaxZoom,
            showCoverageOnHover: s.showCoverageOnHover,
            maxClusterRadius:    80,
          });
        }
        return L.layerGroup();
      }

      function updateTileLayer() {
        if (!map) { return; }
        if (tileLayer) { map.removeLayer(tileLayer); }
        tileLayer = L.tileLayer(ctrl.currentSettings.tileUrl, {
          attribution: getAttribution(ctrl.currentSettings.tileProvider),
          maxZoom: 19,
        }).addTo(map);
      }

      // ── Data loading ──────────────────────────────────────────────────────
      function loadResults() {
        ctrl.loading = true;
        ctrl.error   = null;

        var params = buildApiParams();

        crmApi4('SearchDisplay', 'run', params).then(
          function(results) {
            ctrl.results = results;
            ctrl.loading = false;

            if (angular.isDefined(ctrl.totalCount)) {
              ctrl.totalCount = results.length;
            }

            renderMarkers(results);
          },
          function(err) {
            ctrl.loading = false;
            ctrl.error   = err.error_message || 'Error loading results.';
            console.error('[crmSearchDisplayLeaflet] API error:', err);
          }
        );
      }

      function buildApiParams() {
        return {
          return:      'page:1',
          savedSearch: ctrl.search,
          display:     ctrl.display,
          filters:     angular.copy(ctrl.filters) || {},
          afform:      ctrl.afform || null,
        };
      }

      // ── Marker rendering ──────────────────────────────────────────────────
      function renderMarkers(results) {
        if (!map || !markerLayer) { return; }

        markerLayer.clearLayers();

        var s       = ctrl.currentSettings;
        var latKey  = s.latitudeColumn;
        var lngKey  = s.longitudeColumn;
        var bounds  = [];

        angular.forEach(results, function(row) {
          var lat = parseFloat(row[latKey]);
          var lng = parseFloat(row[lngKey]);

          if (isNaN(lat) || isNaN(lng)) { return; }

          var color  = resolveColor(row);
          var icon   = buildDivIcon(color);
          var marker = L.marker([lat, lng], { icon: icon });

          marker.bindPopup(buildPopupHtml(row), {
            maxWidth:  260,
            className: 'crm-leaflet-popup',
          });

          markerLayer.addLayer(marker);
          bounds.push([lat, lng]);
        });

        // Fit map to markers (skip if viewport filtering is active — user is
        // panning manually in that case).
        if (bounds.length && !ctrl.currentSettings.viewportFilter) {
          viewportPause = true;
          map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
          $timeout(function() { viewportPause = false; }, 500);
        }
      }

      // ── Colour resolution ─────────────────────────────────────────────────
      function resolveColor(row) {
        var s         = ctrl.currentSettings;
        var field     = s.markerColorField;
        var colorMap  = s.colorMap || {};

        if (field && row[field]) {
          return colorMap[row[field]] || '#555555';
        }
        return colorMap['_default'] || '#2a6496';
      }

      // ── Custom div-icon (pin shape) ───────────────────────────────────────
      function buildDivIcon(color) {
        var html = [
          '<div class="crm-map-pin" style="background:' + _.escape(color) + ';',
          'border:2px solid #fff;',
          'box-shadow:0 1px 4px rgba(0,0,0,0.35);">',
          '</div>',
        ].join('');

        return L.divIcon({
          html:        html,
          className:   'crm-map-marker',
          iconSize:    [20, 20],
          iconAnchor:  [10, 20],
          popupAnchor: [0, -22],
        });
      }

      // ── Popup HTML ────────────────────────────────────────────────────────
      function buildPopupHtml(row) {
        var s       = ctrl.currentSettings;
        var label   = row[s.labelColumn]   ? _.escape(row[s.labelColumn])   : '(no name)';
        var subtitle = row[s.subtitleColumn] ? _.escape(row[s.subtitleColumn]) : '';

        var html = '<div class="crm-leaflet-popup-inner">';
        html += '<strong class="crm-popup-title">' + label + '</strong>';

        if (subtitle) {
          html += '<span class="crm-popup-subtitle">' + subtitle + '</span>';
        }

        // Extra columns from display.columns definition.
        if (ctrl.display && ctrl.display.columns) {
          html += '<table class="crm-popup-table">';
          angular.forEach(ctrl.display.columns, function(col) {
            var key = col.key || col.id;
            if (!key || key === s.labelColumn || key === s.subtitleColumn) { return; }
            if (angular.isDefined(row[key]) && row[key] !== null && row[key] !== '') {
              html += '<tr>';
              html += '<td class="crm-popup-label">' + _.escape(col.label || key) + '</td>';
              html += '<td class="crm-popup-value">' + _.escape(String(row[key])) + '</td>';
              html += '</tr>';
            }
          });
          html += '</table>';
        }

        // "View contact" link (works for Contact entity searches).
        if (row.id) {
          var viewUrl = CRM.url('civicrm/contact/view', { cid: row.id, reset: 1 });
          html += '<a class="crm-popup-link" href="' + viewUrl + '" target="_blank">'
               + '<i class="crm-i fa-external-link" aria-hidden="true"></i> View contact</a>';
        }

        html += '</div>';
        return html;
      }

      // ── Attribution helper ────────────────────────────────────────────────
      function getAttribution(provider) {
        var map = {
          osm:          '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
          carto_light:  '&copy; <a href="https://carto.com/attributions">CARTO</a>',
          carto_dark:   '&copy; <a href="https://carto.com/attributions">CARTO</a>',
          stadia_toner: '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>',
        };
        return map[provider] || map.osm;
      }
    },
  });

})(angular, CRM.$, CRM._);
