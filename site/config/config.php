<?php

return [
  'debug' => true,
  'api' => [
    'basicAuth' => false,        // ❌ désactive l'auth
    'allowInsecure' => true      // ✅ accepte HTTP
  ],
  'kql' => [
    'auth' => false,             // ✅ KQL sans login
    'intercept' => function ($type, $key, $value) {
      return true;  // Autorise TOUT en mode dev
    }
  ],
  'hooks' => require __DIR__ . '/hooks.php'
];