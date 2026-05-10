<?php
return [
  'js' => [
    'ang/crmSearchDisplayLeaflet.js',
    'ang/crmSearchDisplayLeaflet/*.js',
  ],
  'css' => [
    'ang/crmSearchDisplayLeaflet.css',
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
