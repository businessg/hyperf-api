<?php

declare(strict_types=1);

namespace BusinessG\HyperfApi;

use BusinessG\HyperfApi\Exception\BusinessApiException;
use BusinessG\HyperfApi\Http\HttpClient;
use BusinessG\HyperfApi\Http\HttpClientInterface;
use BusinessG\HyperfApi\Http\SafeResponseFormatter;
use GuzzleHttp\Middleware;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Logger\LoggerFactory;
use perpower\outApi\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use function Hyperf\Support\make;

abstract class AbstractApi implements ApiInterface
{
    protected array $config = [];

    protected ?HttpClientInterface $httpClient = null;

    protected LoggerInterface $logger;

    public function __construct(protected ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $this->config = Arr::merge(
            $config->get('business_api.common', []),
            $config->get('business_api.apis.' . static::class, [])
        );
        $this->logger = $this->container->get(LoggerFactory::class)->get('business-api.' . static::class);
    }

    public function __call(string $name, array $arguments)
    {
        $apis = $this->getAllApis();
        if (isset($apis[$name])) {
            return $this->request($name, $arguments[0] ?? []);
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
        $apiParam = $apis[$apiKey];
        if (!$apiParam instanceof ApiParam) {
            throw new BusinessApiException(sprintf('The corresponding value of apikey [%s] must be an instance of %s', $apiKey, ApiParam::class));
        }
        return $apiParam;
    }

    final public function getApiParamByKeys(string ...$apiKeys): array
    {
        return array_map([$this, 'getApiParamByKey'], $apiKeys);
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
     * @throws BusinessApiException
     */
    final  public function request(string $apiKey, array $options = [])
    {
        $apiParam = clone $this->getApiParamByKey($apiKey);
        $apiParam->setOptions(Arr::merge($apiParam->getOptions(), $options));
        return $this->requestByApiParam($apiParam, $options);
    }

    /**
     * 批量请求<apiKey>
     *
     * @param array<string>|array<string,array> $apiKeys
     *  `[
     *      "getAllUser",
     *      "getUser"=>[
     *          "json"=>["id"=>1]
     *      ]
     *  ]`
     * @param bool $parallel
     * @return array
     * @throws BusinessApiException
     */
    final public function batchRequest(array $apiKeys, bool $parallel = true): array
    {
        $apiParams = [];
        $keys = [];
        foreach ($apiKeys as $key => $apiKey) {
            if (is_string($apiKey)) {
                $keys[] = $apiKey;
                $apiParam = clone $this->getApiParamByKey($apiKey);
            } elseif (is_array($apiKey)) {
                $keys[] = $key;
                $apiParam = clone $this->getApiParamByKey($key);
                $apiParam->setOptions(Arr::merge($apiParam->getOptions(), $apiKey ?? []));
            } else {
                throw new BusinessApiException('The apikey data type is incorrect');
            }
            $apiParams[] = $apiParam;
        };

        return array_combine($keys, $this->batchRequestByApiParam($apiParams, $parallel));
    }

    /**
     * 发送请求<apiParam>
     *
     * @param ApiParam $apiParam
     * @return ResponseInterface
     */
    final public function requestByApiParam(ApiParam $apiParam)
    {
        $apiParam = $this->beforeRequest($apiParam);
        $mockHandler = $apiParam->isMock() ? $apiParam->getMockHandler() : null;
        return $this->getHttpClient()->request($apiParam->getMethod(), $apiParam->getUri(), $apiParam->getOptions(), $mockHandler);
    }

    /**
     * 批量请求<apiParam>
     *
     * @param array $apiParams
     * @param bool $parallel
     * @return ResponseInterface[]
     */
    final public function batchRequestByApiParam(array $apiParams, bool $parallel = true)
    {
        $requests = array_map(function (ApiParam $apiParam) {
            $apiParam = $this->beforeRequest($apiParam);
            return [
                'uri' => $apiParam->getUri(),
                'method' => $apiParam->getMethod(),
                'mockHandler' => $apiParam->isMock() ? $apiParam->getMockHandler() : null,
                'options' => $apiParam->getOptions(),
            ];
        }, $apiParams);
        return $this->getHttpClient()->batchRequest($requests, $parallel);
    }

    protected function getHttpClient(): HttpClientInterface
    {
        if (!$this->httpClient instanceof HttpClientInterface) {
            $this->httpClient = make(HttpClient::class, [
                'config' => $this->getConfig()['http'] ?? [],
            ]);
            // 设置中间件
            $this->httpClient->addMiddlewares($this->getMiddlewares());
        }
        return $this->httpClient;
    }

    public function middlewares(): array
    {
        return [];
    }

    private function getMiddlewares()
    {
        return array_merge($this->defautMiddlewares(), $this->middlewares());
    }

    /**
     * 默认中间件
     *
     * @return array
     */
    private function defautMiddlewares(): array
    {
        return array_filter([
            $this->getLogMiddleware(),
        ]);
    }

    private function getLogMiddleware()
    {
        $config = $this->getConfig()['logger'] ?? [];
        $enable = $config['enable'] ?? true;
        if (!$enable) {
            return null;
        }
        $formatter = new SafeResponseFormatter($config['messageFormatter'] ?? SafeResponseFormatter::CLF);
        return Middleware::log($this->logger, $formatter, $config['logLevel'] ?? LogLevel::INFO);
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}