<?php

return [
  'debug' => true,
  'api' => [
    'basicAuth' => false,        // ❌ désactive l'auth
    'allowInsecure' => true      // ✅ accepte HTTP
  ],
  'kql' => [
    'auth' => false,             // ✅ KQL sans login
    'intercept' => true          // ✅ Autorise toutes les méthodes (dev only)
  ],
  'hooks' => require __DIR__ . '/hooks.php'
];