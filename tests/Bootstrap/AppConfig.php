<?php

namespace Culpa\Tests\Bootstrap;

use Culpa\Facades\Schema;

return [
    'app' => [
        'aliases' => [
            'Schema' => Schema::class,
        ],
    ],
    'database' => [
        'default' => 'sqlite',
        'connections' => [
            'sqlite' => [
                'database' => ':memory:',
                'driver' => 'sqlite',
                'prefix' => '',
            ],
        ],
    ],
];
