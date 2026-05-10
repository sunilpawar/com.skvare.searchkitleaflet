<?php

use CRM_SearchKitLeaflet_ExtensionUtil as E;

// Registers 'leaflet_map' as a SearchKit display type option value.
// The 'name' field must match the Angular element directive exported by
// the crmSearchDisplayLeaflet module.
return [
  [
    'name'   => 'SearchDisplayType:leaflet_map',
    'entity' => 'OptionValue',
    'cleanup' => 'always',
    'update'  => 'always',
    'params'  => [
      'version' => 4,
      'values'  => [
        'option_group_id.name' => 'search_display_type',
        'value'       => 'leaflet_map',
        'name'        => 'crm-search-display-leaflet',
        'label'       => E::ts('Leaflet Map'),
        'description' => E::ts('Display results on an interactive Leaflet map with clustering and viewport filtering.'),
        'icon'        => 'fa-map-marker',
        'is_reserved' => FALSE,
        'is_active'   => TRUE,
      ],
      'match' => ['option_group_id', 'name'],
    ],
  ],
];
