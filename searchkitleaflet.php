<?php

/**
 * @file
 * SearchKit Leaflet Map - CiviCRM Extension
 *
 * Adds an interactive Leaflet map display type to SearchKit.
 *
 * @package  com.skvare.searchkitleaflet
 * @author   Skvare <info@skvare.com>
 * @license  AGPL-3.0
 */

require_once 'searchkitleaflet.civix.php';

use CRM_SearchKitLeaflet_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 */
function searchkitleaflet_civicrm_config(&$config): void {
  _searchkitleaflet_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 */
function searchkitleaflet_civicrm_install(): void {
  _searchkitleaflet_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 */
function searchkitleaflet_civicrm_enable(): void {
  _searchkitleaflet_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Registers the Angular module that renders the Leaflet map display and
 * provides the admin settings panel component (searchAdminDisplayLeafletMap).
 *
 * The AngularDependencyInjector in SearchKit reads the 'exports' key and
 * automatically adds this module as a dependency of crmSearchDisplay (and
 * transitively crmSearchAdmin) whenever the leaflet_map display type is active.
 *
 * @param array $angularModules
 */
function searchkitleaflet_civicrm_angularModules(array &$angularModules): void {
  // ── crmLeaflet ────────────────────────────────────────────────────────────
  // Pure vendor-library module.  Any extension that needs the Leaflet API
  // (window.L) just adds 'crmLeaflet' to its own 'requires' array.
  // The assets are loaded on-demand — never globally.
  $angularModules['crmLeaflet'] = [
    'ext' => 'com.skvare.searchkitleaflet',
    'js'  => [
      'ext/leaflet/leaflet.js',
      'ext/leaflet/leaflet.markercluster.js',
      'ang/crmLeaflet/crmLeaflet.module.js',
    ],
    'css' => [
      'ext/leaflet/leaflet.css',
      'ext/leaflet/MarkerCluster.css',
      'ext/leaflet/MarkerCluster.Default.css',
    ],
    'requires' => [],
  ];

  // ── crmSearchDisplayLeaflet ───────────────────────────────────────────────
  // SearchKit display + admin-settings components.  Requires crmLeaflet so
  // the Leaflet API is guaranteed to be present when the component runs.
  //
  // AngularDependencyInjector reads 'exports' and automatically adds this
  // module as a dependency of crmSearchDisplay (and transitively crmSearchAdmin)
  // whenever the leaflet_map display type is active.
  $angularModules['crmSearchDisplayLeaflet'] = [
    'ext'  => 'com.skvare.searchkitleaflet',
    'js'   => [
      'ang/crmSearchDisplayLeaflet/crmSearchDisplayLeaflet.module.js',
      'ang/crmSearchDisplayLeaflet/crmSearchDisplayLeaflet.component.js',
      // Registers searchAdminDisplayLeafletMap into the crmSearchAdmin module,
      // matching the <search-admin-display-leaflet_map> element the admin
      // template generates.
      'ang/crmSearchDisplayLeaflet/crmSearchDisplayLeafletSettings.component.js',
    ],
    'css'  => [
      'ang/crmSearchDisplayLeaflet/crmSearchDisplayLeaflet.css',
    ],
    'partials' => [
      'ang/crmSearchDisplayLeaflet',
    ],
    'requires' => [
      'crmLeaflet',
      'crmSearchDisplay',
      'crmUi',
      'crmUtil',
    ],
    'exports' => [
      'crm-search-display-leaflet' => 'E',
    ],
  ];
}
