<?php

return [
    'default' => 'sqlsrv',
    'connections' => [
        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ],
        'sqlsrv2' => [
            'driver' => 'sqlsrv',
            'host' => env('DB2_HOST'),
            'database' => env('DB2_DATABASE'),
            'username' => env('DB2_USERNAME'),
            'password' => env('DB2_PASSWORD'),
        ],
    ]
];
