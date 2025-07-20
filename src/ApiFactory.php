<?php

declare(strict_types=1);

namespace BusinessG\HyperfApi;

use BusinessG\HyperfApi\Exception\BusinessApiException;
use BusinessG\HyperfApi\Exception\InvalidApiException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use Psr\Container\ContainerInterface;
use function Hyperf\Support\make;

class ApiFactory
{
    protected array $apis = [];
    protected array $config = [];

    public function __construct(
        protected ContainerInterface $container,
    )
    {
        $config = $container->get(ConfigInterface::class);
        $this->config = $config->get('business_api');
    }

    public function get(string $apiClass): AbstractApi
    {
        if (!class_exists($apiClass)) {
            throw new \InvalidArgumentException("API class {$apiClass} not found");
        }

        $api = $this->apis[$apiClass] ?? null;
        if (!$api instanceof ApiInterface) {
            $api = make($apiClass);
            if (!$api instanceof ApiInterface) {
                throw new InvalidApiException(sprintf('[Error] class %s is not instanceof %s.', $apiClass, ApiInterface::class));
            }
            $this->apis[$apiClass] = $api;
        }
        return $api;
    }
}