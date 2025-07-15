<?php

namespace BusinessG\HyperfApi;

use Hyperf\Contract\ConfigInterface;
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
              return  call_user_func([$this, 'request'], ...$arguments);
            }
        }
    }

    abstract public function apis(): array;


    public function test(ApiParam $apiParam)
    {
        var_dump('test');

    }
    public function getApiKeys()
    {
        return array_keys($this->apis());
    }

    public function middlewares(): array
    {
        return [];
    }

    public function beforeRequest()
    {
        // TODO: Implement beforeRequest() method.
    }

    public function request(string $apiKey, object $apiParam)
    {
        var_dump('我进来了');
        // TODO: Implement request() method.
    }

    public function batchRequest(array $apiKeys)
    {
        // TODO: Implement batchRequest() method.
    }

    public function isMock(): bool
    {
        return false;
    }

    public function getConfig(): array
    {
        return  $this->config;
    }
}