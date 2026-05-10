<?php
return [
  'js' => [
    'ang/crmSearchDisplayLeafletSettings.js',
    'ang/crmSearchDisplayLeafletSettings/*.js',
  ],
  'partials' => [
    'ang/crmSearchDisplayLeafletSettings',
  ],
  'requires' => [
    'crmSearchAdmin',
    'crmSearchDisplayLeaflet',
  ],
  'basePages' => ['civicrm/admin/search'],
];
