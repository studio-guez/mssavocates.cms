<?php

return [
  'debug' => true,
  'api' => [
    'basicAuth' => false,        // ❌ désactive l'auth
    'allowInsecure' => true      // ✅ accepte HTTP
  ],
  'kql' => [
    'auth' => false              // ✅ KQL sans login
  ]
];