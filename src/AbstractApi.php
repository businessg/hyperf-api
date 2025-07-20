<?php

declare(strict_types=1);

namespace BusinessG\HyperfApi;

use BusinessG\HyperfApi\Exception\BusinessApiException;
use BusinessG\HyperfApi\Http\HttpClient;
use BusinessG\HyperfApi\Http\HttpClientInterface;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use function Hyperf\Support\make;

abstract class AbstractApi implements ApiInterface
{
    protected array $config = [];

    protected ?HttpClientInterface $httpClient = null;

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

    final public function getApiParamByKey(string $apiKey): ApiParam
    {
        $apis = $this->getAllApis();
        if (!isset($apis[$apiKey])) {
            throw new BusinessApiException(sprintf('api key not exists[%s]', $apiKey));
        }
        if (!$apiParam instanceof ApiParam) {
            throw new BusinessApiException(sprintf('The corresponding value of apikey[%s] must be an instance of %s', $apiKey, ApiParam::class));
        }
        return $apiParam;
    }

    final public function getApiParamByKeys(string ...$apiKeys): array
    {
        return array_map(fn($apiKey) => $this->getApiParamByKey($apiKey), $apiKeys);
    }


    /**
     * @return array<string, ApiParam>
     */
    public function apis(): array
    {
        return [];
    }


    public function beforeRequest(ApiParam $apiParam): ApiParam
    {
        // 可改变apiParam的信息,比如options
        return $apiParam;
    }

    /**
     * 发送请求<apiKey>
     *
     * @param string $apiKey
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request(string $apiKey)
    {
        return $this->requestByApiParam($this->beforeRequest($this->getApiParamByKey($apiKey)));
    }

    /**
     * 批量请求<apiKey>
     *
     * @param array $apiKeys
     * @param bool $parallel
     * @return array
     */
    public function batchRequest(array $apiKeys, bool $parallel = true): array
    {
        return $this->batchRequestByApiParam($this->getApiParamByKeys(...$apiKeys), $parallel);
    }

    /**
     *  发送请求<apiParam>
     *
     * @param ApiParam $apiParam
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestByApiParam(ApiParam $apiParam)
    {
        $mockHandle = $apiParam->isMock() ? $apiParam->getMockHandle() : null;
        return $this->httpClient->request($apiParam->getMethod(), $apiParam->getUri(), $apiParam->getOptions(), $mockHandle);
    }

    /**
     * 批量请求<apiParam>
     *
     * @param ApiParam $apiParam
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function batchRequestByApiParam(array $apiParams, bool $parallel = true)
    {
        $requests = array_map(function (ApiParam $apiParam) {
            $apiParam = $this->beforeRequest($apiParam);
            return [
                'uri' => $apiParam->getUri(),
                'method' => $apiParam->getMethod(),
                'mockHander' => $apiParam->isMock() ? $apiParam->getMockHandle() : null,
                'options' => $apiParam->getOptions(),
            ];
        }, $apiParams);
        return $this->httpClient->batchRequest($apiParams, $parallel);
    }

    protected function getHttpClient(): HttpClientInterface
    {
        if ($this->httpClient instanceof HttpClientInterface) {
            $this->httpClient = make(HttpClient::class, [
                'config' => $this->getConfig()['http']??[],
            ]);
        }
        return $this->httpClient;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}