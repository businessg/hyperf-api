<?php

declare(strict_types=1);

namespace BusinessG\HyperfApi;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractApi implements ApiInterface
{
    protected array $config = [];

    public function __construct(protected ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $this->config = $config->get('business_api.apis.' . static::class, []);
    }

    public function __call(string $name, array $arguments)
    {
        $apis = $this->getAllApis();
        if (isset($apis[$name])) {
            $options = $arguments[0] ?? [];
            return $this->request($name, $options ?? []);
        }

        throw new \BadMethodCallException("Method {$name} not found");
    }

    /**
     * api
     *
     * @return array<string, ApiParam>
     */
    final public function getAllApis(): array
    {
        return array_merge($this->apis(), $this->getConfig()['apis'] ?? []);
    }

    /**
     * @return array<string, ApiParam>
     */
    public function apis(): array
    {
        return [];
    }



    public function beforeRequest()
    {
        // TODO: Implement beforeRequest() method.
    }

    public function request(string $apiKey, array $options = [])
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
        return $this->config;
    }

}