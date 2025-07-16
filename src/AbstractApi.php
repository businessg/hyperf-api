<?php

declare(strict_types=1);

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

    public function __call(string $name, array $arguments)
    {
        $apis = $this->apis();
        if (isset($apis[$name])) {
            $params = $arguments[0] ?? [];
            $options = $arguments[1] ?? [];
            return $this->request($name, $params, $options);
        }

        throw new \BadMethodCallException("Method {$name} not found");
    }

    abstract public function apis(): array;

    public function middlewares(): array
    {
        return [];
    }

    public function beforeRequest()
    {
        // TODO: Implement beforeRequest() method.
    }

    public function request(string $apiKey)
    {

    }

    public function requestApiParam(ApiParam $apiParam, array $options = [])
    {
        // TODO: Implement requestApiParam() method.
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