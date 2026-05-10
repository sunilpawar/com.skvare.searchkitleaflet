<?php

/**
 * @file
 * PHPUnit test: verifies the Leaflet Map display type is correctly registered
 * and that its required keys are present.
 *
 * Run with:
 *   cd <civicrm-root>
 *   phpunit --filter CRM_SearchKitLeaflet_DisplayTypeTest
 */

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;

/**
 * @group headless
 */
class CRM_SearchKitLeaflet_DisplayTypeTest extends \PHPUnit\Framework\TestCase
  implements HeadlessInterface, HookInterface {

  public function setUpHeadless(): \Civi\Test\CiviEnvBuilder {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  // ── Display-type hook tests ─────────────────────────────────────────────

  public function testDisplayTypeIsRegistered(): void {
    $displayTypes = [];
    \CRM_Utils_Hook::singleton()->invoke(
      ['displayTypes'],
      $displayTypes,
      \CRM_Utils_Hook::$nullObject,
      \CRM_Utils_Hook::$nullObject,
      \CRM_Utils_Hook::$nullObject,
      \CRM_Utils_Hook::$nullObject,
      'civicrm_searchKitDisplayTypes'
    );

    $this->assertArrayHasKey('leaflet_map', $displayTypes,
      'leaflet_map display type must be present after hook fires.'
    );
  }

  public function testDisplayTypeHasRequiredKeys(): void {
    $displayTypes = [];
    searchkitleaflet_civicrm_searchKitDisplayTypes($displayTypes);

    $type = $displayTypes['leaflet_map'];

    foreach (['id', 'name', 'label', 'angularDirective', 'icon'] as $key) {
      $this->assertArrayHasKey($key, $type,
        "Display type must contain key '$key'."
      );
      $this->assertNotEmpty($type[$key],
        "Display type key '$key' must not be empty."
      );
    }
  }

  public function testAngularDirectiveName(): void {
    $displayTypes = [];
    searchkitleaflet_civicrm_searchKitDisplayTypes($displayTypes);

    $this->assertSame(
      'crm-search-display-leaflet',
      $displayTypes['leaflet_map']['angularDirective'],
      'angularDirective must match the Angular component selector.'
    );
  }

  public function testDefaultSettingsExist(): void {
    $displayTypes = [];
    searchkitleaflet_civicrm_searchKitDisplayTypes($displayTypes);

    $settings = $displayTypes['leaflet_map']['settings'] ?? [];

    $this->assertArrayHasKey('latitudeColumn', $settings);
    $this->assertArrayHasKey('longitudeColumn', $settings);
    $this->assertArrayHasKey('labelColumn', $settings);
    $this->assertArrayHasKey('enableClustering', $settings);
    $this->assertArrayHasKey('viewportFilter', $settings);
    $this->assertArrayHasKey('colorMap', $settings);
    $this->assertIsArray($settings['colorMap']);
  }

  // ── DisplayType PHP class tests ─────────────────────────────────────────

  public function testNormaliseSettingsMergesDefaults(): void {
    $normalised = \Civi\SearchKitLeaflet\DisplayType::normaliseSettings([]);

    $this->assertSame('address_primary.geo_code_1', $normalised['latitudeColumn']);
    $this->assertSame('address_primary.geo_code_2', $normalised['longitudeColumn']);
    $this->assertIsBool($normalised['enableClustering']);
    $this->assertIsInt($normalised['mapHeight']);
    $this->assertArrayHasKey('tileUrl', $normalised);
  }

  public function testNormaliseSettingsOverridesDefaults(): void {
    $normalised = \Civi\SearchKitLeaflet\DisplayType::normaliseSettings([
      'latitudeColumn' => 'custom_lat',
      'mapHeight'      => '800',
      'tileProvider'   => 'carto_light',
    ]);

    $this->assertSame('custom_lat', $normalised['latitudeColumn']);
    $this->assertSame(800, $normalised['mapHeight']);
    $this->assertStringContainsString('cartocdn.com', $normalised['tileUrl']);
  }

  public function testNormaliseSettingsFallsBackToOsmForUnknownProvider(): void {
    $normalised = \Civi\SearchKitLeaflet\DisplayType::normaliseSettings([
      'tileProvider' => 'unknown_provider',
    ]);

    $this->assertStringContainsString('openstreetmap.org', $normalised['tileUrl']);
  }

  public function testGetTileUrl(): void {
    $url = \Civi\SearchKitLeaflet\DisplayType::getTileUrl('carto_dark');
    $this->assertStringContainsString('cartocdn.com', $url);

    $fallback = \Civi\SearchKitLeaflet\DisplayType::getTileUrl('nonexistent');
    $this->assertStringContainsString('openstreetmap.org', $fallback);
  }

  // ── Angular module registration tests ──────────────────────────────────

  public function testAngularModuleIsRegistered(): void {
    $modules = [];
    searchkitleaflet_civicrm_angularModules($modules);

    $this->assertArrayHasKey('crmSearchDisplayLeaflet', $modules);

    $mod = $modules['crmSearchDisplayLeaflet'];
    $this->assertSame('com.skvare.searchkitleaflet', $mod['ext']);
    $this->assertNotEmpty($mod['js']);
    $this->assertNotEmpty($mod['css']);
    $this->assertNotEmpty($mod['partials']);
  }

}
