<?php

declare(strict_types=1);

return [
    'apis' => [
        \App\Api\DemoApi::class => [
            'http' => [
                'base_url' => 'http://www.demo.com'
            ],
        ],
    ],
];
