<?php

declare(strict_types=1);

namespace BusinessG\HyperfApi;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use Psr\Container\ContainerInterface;

class ApiFactory
{
    public function __construct(
        protected ContainerInterface $container,
        protected ClientFactory $clientFactory,
        protected ConfigInterface $config
    ) {
    }

    public function make(string $apiClass): AbstractApi
    {
        if (!class_exists($apiClass)) {
            throw new \InvalidArgumentException("API class {$apiClass} not found");
        }

        return new $apiClass(
            $this->container,
            $this->clientFactory,
            $this->config
        );
    }
}