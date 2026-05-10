<?php

namespace Civi\SearchKitLeaflet;

/**
 * Helper service for the Leaflet Map display type.
 *
 * Validates and normalises display settings before they are passed to the
 * Angular component.
 *
 * @package Civi\SearchKitLeaflet
 */
class DisplayType {

  /**
   * Default color map keyed by contact_type value.
   */
  const DEFAULT_COLOR_MAP = [
    'Individual'   => '#2a6496',
    'Organization' => '#c0392b',
    'Household'    => '#e67e22',
  ];

  /**
   * Default settings applied when a new Leaflet Map display is created.
   */
  const DEFAULTS = [
    'latitudeColumn'      => 'address_primary.geo_code_1',
    'longitudeColumn'     => 'address_primary.geo_code_2',
    'labelColumn'         => 'display_name',
    'subtitleColumn'      => 'address_primary.city',
    'markerColorField'    => 'contact_type',
    'colorMap'            => self::DEFAULT_COLOR_MAP,
    'enableClustering'    => TRUE,
    'spiderfyOnMaxZoom'   => TRUE,
    'showCoverageOnHover' => FALSE,
    'viewportFilter'      => TRUE,
    'tileProvider'        => 'osm',
    'mapHeight'           => 500,
  ];

  /**
   * Tile provider URL templates.
   */
  const TILE_PROVIDERS = [
    'osm'          => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    'carto_light'  => 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
    'carto_dark'   => 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
    'stadia_toner' => 'https://tiles.stadiamaps.com/tiles/stamen_toner/{z}/{x}/{y}{r}.png',
  ];

  /**
   * Merge saved display settings with defaults, ensuring all keys exist.
   *
   * @param array $settings  Raw settings from the display record.
   *
   * @return array  Normalised settings array.
   */
  public static function normaliseSettings(array $settings): array {
    $merged = array_merge(self::DEFAULTS, $settings);

    // Ensure colorMap is an array.
    if (!is_array($merged['colorMap'])) {
      $merged['colorMap'] = self::DEFAULT_COLOR_MAP;
    }

    // Resolve tile URL.
    $provider = $merged['tileProvider'] ?? 'osm';
    if (!isset(self::TILE_PROVIDERS[$provider])) {
      $provider = 'osm';
    }
    $merged['tileUrl'] = self::TILE_PROVIDERS[$provider];

    // Cast numerics and booleans.
    $merged['mapHeight']           = (int) $merged['mapHeight'];
    $merged['enableClustering']    = (bool) $merged['enableClustering'];
    $merged['spiderfyOnMaxZoom']   = (bool) $merged['spiderfyOnMaxZoom'];
    $merged['showCoverageOnHover'] = (bool) $merged['showCoverageOnHover'];
    $merged['viewportFilter']      = (bool) $merged['viewportFilter'];

    return $merged;
  }

  /**
   * Return the tile URL for a given provider key.
   *
   * @param string $provider  Key from TILE_PROVIDERS.
   *
   * @return string
   */
  public static function getTileUrl(string $provider = 'osm'): string {
    return self::TILE_PROVIDERS[$provider] ?? self::TILE_PROVIDERS['osm'];
  }

}
