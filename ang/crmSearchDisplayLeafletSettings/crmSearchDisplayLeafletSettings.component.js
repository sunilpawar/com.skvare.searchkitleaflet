(function(angular, $, _) {
  'use strict';

  /**
   * searchAdminDisplayLeafletMap component.
   *
   * Registered in the crmSearchDisplayLeafletSettings module so that SearchKit's
   * admin template can resolve <search-admin-display-leaflet_map> (Angular
   * normalises the underscore to camelCase: searchAdminDisplayLeafletMap).
   *
   * Bindings mirror the other searchAdminDisplay* components:
   *   display   - the SearchDisplay record being edited (passed by ref; we
   *               modify display.settings in-place)
   *   apiEntity - the primary entity of the saved search
   *   apiParams - savedSearch.api_params (contains the 'select' array)
   */
  angular.module('crmSearchDisplayLeafletSettings').component('searchAdminDisplayLeafletMap', {

    bindings: {
      display:   '<',
      apiEntity: '<',
      apiParams: '<',
    },

    templateUrl: '~/crmSearchDisplayLeafletSettings/crmSearchDisplayLeafletSettings.html',

    controller: function($scope) {
      var ctrl = this;

      // ── Defaults ──────────────────────────────────────────────────────────
      var DEFAULTS = {
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
        mapHeight:           500,
      };

      ctrl.tileProviders = [
        { key: 'osm',          label: 'OpenStreetMap' },
        { key: 'carto_light',  label: 'Carto Light' },
        { key: 'carto_dark',   label: 'Carto Dark' },
        { key: 'stadia_toner', label: 'Stadia Toner' },
      ];

      // ── Lifecycle ─────────────────────────────────────────────────────────
      ctrl.$onInit = function() {
        ctrl.display.settings = angular.merge({}, DEFAULTS, ctrl.display.settings || {});
        ctrl.colorRows = buildColorRows(ctrl.display.settings.colorMap);
        ctrl.columns   = buildColumnList();
      };

      // ── Column list ───────────────────────────────────────────────────────
      // Parses apiParams.select expressions into {key, label} pairs.
      // Each expression is either a bare field name ("display_name",
      // "address_primary.geo_code_1") or has an alias ("foo AS bar").
      // The key used in result rows is the alias (or the full expression when
      // there is no alias), so that is what we store as the option value.
      function parseSelectKey(expr) {
        var match = String(expr).match(/\bAS\s+(\w+)\s*$/i);
        return match ? match[1] : expr.trim();
      }

      function buildColumnList() {
        var cols = [{ key: '', label: '-- select column --' }];
        if (ctrl.apiParams && ctrl.apiParams.select) {
          angular.forEach(ctrl.apiParams.select, function(expr) {
            var key = parseSelectKey(expr);
            cols.push({ key: key, label: key });
          });
        }
        return cols;
      }

      // ── Colour-map UI helpers ─────────────────────────────────────────────
      function buildColorRows(colorMap) {
        var rows = [];
        angular.forEach(colorMap, function(hex, value) {
          rows.push({ value: value, color: hex });
        });
        return rows;
      }

      function syncColorMap() {
        var map = {};
        angular.forEach(ctrl.colorRows, function(row) {
          if (row.value) {
            map[row.value] = row.color || '#555555';
          }
        });
        ctrl.display.settings.colorMap = map;
      }

      ctrl.addColorRow = function() {
        ctrl.colorRows.push({ value: '', color: '#2a6496' });
      };

      ctrl.removeColorRow = function(index) {
        ctrl.colorRows.splice(index, 1);
        syncColorMap();
      };

      ctrl.onColorChange = function() {
        syncColorMap();
      };
    },
  });

})(angular, CRM.$, CRM._);
