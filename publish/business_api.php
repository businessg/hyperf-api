<?php

declare(strict_types=1);

use Psr\Log\LogLevel;
use BusinessG\HyperfApi\Http\SafeResponseFormatter;

return [
    // 全局配置
    'common' => [
        // https://docs.guzzlephp.org/en/stable/request-options.html
        'http' => [
            'timeout' => 10, // 秒
            'connect_timeout' => 10,// 秒
            'proxy' => [],
        ],
        'logger' => [
            'enable' => true,
            'messageFormatter' => SafeResponseFormatter::DEBUG,
            'logLevel' => LogLevel::INFO
        ],
    ],
    // api独立配置
    'apis' => [
        \App\Api\DemoApi::class => [
            'http' => [
                'base_url' => 'http://www.demo.com'
            ],
        ],
    ],
];
