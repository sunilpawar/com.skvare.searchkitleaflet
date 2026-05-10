<?php

/**
 * @file
 * Civix-generated helper functions for the extension lifecycle.
 *
 * @package com.skvare.searchkitleaflet
 */

use CRM_SearchKitLeaflet_ExtensionUtil as E;

/**
 * Wrapper for CRM_Extension_System::singleton()->getMapper().
 */
class CRM_SearchKitLeaflet_ExtensionUtil {

  const SHORT_NAME  = 'searchkitleaflet';
  const LONG_NAME   = 'com.skvare.searchkitleaflet';
  const CLASS_PREFIX = 'CRM_SearchKitLeaflet';

  /**
   * Translate a string using this extension's domain.
   */
  public static function ts(string $text, array $params = []): string {
    return ts($text, ['domain' => [self::LONG_NAME, NULL]] + $params);
  }

  /**
   * Get an absolute path to a file within the extension.
   */
  public static function path(string $relative = ''): string {
    static $dir = NULL;
    if ($dir === NULL) {
      $dir = \CRM_Extension_System::singleton()
        ->getMapper()
        ->keyToBasePath(self::LONG_NAME);
    }
    return $dir . ($relative ? DIRECTORY_SEPARATOR . $relative : '');
  }

  /**
   * Get the URL to a resource within the extension.
   */
  public static function url(string $relative = ''): string {
    static $url = NULL;
    if ($url === NULL) {
      $url = \CRM_Extension_System::singleton()
        ->getMapper()
        ->keyToUrl(self::LONG_NAME);
    }
    return $url . ($relative ? '/' . $relative : '');
  }

}

function _searchkitleaflet_civix_civicrm_config(&$config): void {
  static $configured = FALSE;
  if ($configured) {
    return;
  }
  $configured = TRUE;

  $extRoot = dirname(__FILE__) . DIRECTORY_SEPARATOR;
  $include_path = $extRoot . DIRECTORY_SEPARATOR . 'Civi' . PATH_SEPARATOR . get_include_path();
  set_include_path($include_path);
}

function _searchkitleaflet_civix_civicrm_install(): void {}

function _searchkitleaflet_civix_civicrm_enable(): void {}
