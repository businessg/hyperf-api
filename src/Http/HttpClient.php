<?php

declare(strict_types=1);

namespace BusinessG\HyperfApi\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Guzzle\HandlerStackFactory;
use PHPUnit\Event\Code\Throwable;
use Psr\Http\Message\ResponseInterface;

class HttpClient implements HttpClientInterface
{
    protected array $config;
    protected array $middlewares = [];

    public function __construct(
        protected ClientFactory       $clientFactory,
        protected HandlerStackFactory $stackFactory,
        array                         $config = []
    )
    {
        $this->config = $config;
    }

    /**
     * 发送HTTP请求
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @param MockHandler|null $mockHandler
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(
        string       $method,
        string       $uri,
        array        $options = [],
        ?MockHandler $mockHandler = null
    ): ResponseInterface
    {
        if ($mockHandler !== null) {
            return $this->mockRequest($method, $uri, $options, $mockHandler);
        }
        return $this->getClient()->request($method, $uri, $options);
    }

    /**
     * 批量发送HTTP请求
     *
     * @param array $requests
     * `[
     *      [
     *          'method' => 'GET',
     *          'uri' => '/path',
     *          'options' => [],
     *          'mockHandler' => MockHandler|null // 可选
     *      ],
     *      ...
     *  ]`
     * @param bool $parallel 是否并行执行
     * @return ResponseInterface[]|Throwable[]
     */
    public function batchRequest(array $requests, bool $parallel = true): array
    {
        if ($parallel) {
            return $this->parallelBatchRequest($requests);
        }
        return $this->sequentialBatchRequest($requests);
    }

    /**
     * 并行批量请求
     *
     * @param array $requests
     * @return array
     */
    protected function parallelBatchRequest(array $requests): array
    {
        $promises = [];
        foreach ($requests as $key => $request) {
            $mockHandler = $request['mockHandler'] ?? null;
            if ($mockHandler !== null) {
                $client = $this->createMockClient($mockHandler);
            } else {
                $client = $this->getClient();
            }

            $promises[$key] = $client->requestAsync(
                $request['method'],
                $request['uri'],
                $request['options'] ?? []
            );
        }

        $responses = Promise\Utils::settle($promises)->wait();
        return array_map(fn($r) => $r['state'] === 'fulfilled' ? $r['value'] : $r['reason'], $responses);
    }

    /**
     * 顺序批量请求
     *
     * @param array $requests
     * @return array
     */
    protected function sequentialBatchRequest(array $requests): array
    {
        $results = [];
        foreach ($requests as $key => $request) {
            try {
                $mockHandler = $request['mockHandler'] ?? null;

                if ($mockHandler !== null) {
                    $client = $this->createMockClient($clonedHandler);
                } else {
                    $client = $this->getClient();
                }

                $results[$key] = $client->request(
                    $request['method'],
                    $request['uri'],
                    $request['options'] ?? []
                );
            } catch (\Throwable $e) {
                $results[$key] = $e;
            }
        }
        return $results;
    }

    /**
     * 处理Mock请求
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @param MockHandler $mockHandler
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function mockRequest(
        string      $method,
        string      $uri,
        array       $options,
        MockHandler $mockHandler
    ): ResponseInterface
    {
        $client = $this->createMockClient($clonedHandler);
        return $client->request($method, $uri, $options);
    }

    /**
     * 创建临时Mock客户端
     *
     * @param MockHandler $mockHandler
     * @return Client
     */
    protected function createMockClient(MockHandler $mockHandler): Client
    {
        $handlerStack = new HandlerStack($mockHandler);
        foreach ($this->getMiddlewares() as $middleware) {
            $handlerStack->push($middleware, is_string($name) ? $name : '');
        }
        return new Client([
            'handler' => $handlerStack,
        ]);
    }

    /**
     * 刷新客户端实例
     *
     * @return void
     */
    protected function getClient(): Client
    {
        $handlerStack = $this->stackFactory->create();
        foreach ($this->getMiddlewares() as $name => $middleware) {
            $handlerStack->push($middleware, is_string($name) ? $name : '');
        }
        return $this->clientFactory->create([
            ...$this->config,
            'handler' => $handlerStack,
        ]);
    }

    /**
     * 获取全局中间件
     *
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * 添加中间件
     *
     * @param array $middlewares
     * @return $this
     */
    public function addMiddlewares(array $middlewares): static
    {
        $this->middlewares = array_merge($this->middlewares, $middlewares);
        return $this;
    }
}