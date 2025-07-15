<?php

namespace BusinessG\HyperfApi;

use Psr\Container\ContainerInterface;

abstract  class AbstractApi implements ApiInterface
{
    protected array $config = [];

    public function __construct(protected ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $this->config = $config->get('business_api.apis.' . static::class, []);
    }

    public function __call(string $name, array $arguments): mixed
    {
        if (!method_exists($this, $name)) {
            if (in_array($name, $this->getApiKeys())) {
                return call_user_func_array([$this, 'request'], [
                    'apiKey' => $name,
                    'apiParam' => $arguments[0],
                ]);
            }
        }
    }

    public function apis(): array
    {
        // TODO: Implement apis() method.
    }

    public function getApiKeys()
    {
        return array_keys($this->apis());
    }

    public function middlewares(): array
    {
        // TODO: Implement middlewares() method.
    }

    public function beforeRequest()
    {
        // TODO: Implement beforeRequest() method.
    }

    public function request(string $apiKey, object $apiParam)
    {
        // TODO: Implement request() method.
    }

    public function batchRequest(array $apiKeys)
    {
        // TODO: Implement batchRequest() method.
    }

    public function isMock(): bool
    {
        // TODO: Implement isMock() method.
    }

    public function getConfig(): array
    {
        return  $this->config;
    }
}