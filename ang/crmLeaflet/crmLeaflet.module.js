(function(angular) {
  'use strict';

  // Thin Angular wrapper around the Leaflet vendor library.
  // Declaring this module is enough to ensure leaflet.js and its CSS are
  // loaded before any dependent module initialises.
  // Usage: add 'crmLeaflet' to the 'requires' array of your Angular module.
  angular.module('crmLeaflet', []);

})(angular);
