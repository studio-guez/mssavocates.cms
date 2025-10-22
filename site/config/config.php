<?php

header("Access-Control-Allow-Origin: *");


return [
    'url' => '/',
    'api' => [
        'basicAuth' => true,
        'allowInsecure' => false,
    ],
    'debug' => false,
    'kql' => [
        'auth' => false
    ]
];
