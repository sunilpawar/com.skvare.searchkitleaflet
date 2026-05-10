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

