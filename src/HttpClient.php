<?php

declare(strict_types=1);

namespace Businessg\HyperfApi;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Guzzle\HandlerStackFactory;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    protected Client $client;
    protected array $config;
    protected array $middlewares = [];

    public function __construct(
        protected ClientFactory       $clientFactory,
        protected HandlerStackFactory $stackFactory,
        array                         $config = []
    )
    {
        $this->config = $config;
        $this->refreshClient();
    }

    /**
     * 发送HTTP请求
     *
     * @param string $method 请求方法
     * @param string $uri 请求URI
     * @param array $options 请求选项
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        return $this->client->request($method, $uri, $options);
    }

    /**
     * mock请求
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @param MockHandler $mockHandler
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function mockRequest(string $method, string $uri, array $options, MockHandler $mockHandler): ResponseInterface
    {
        $handlerStack = new HandlerStack($mockHandler);
        foreach ($this->middlewares as $middleware) {
            $handlerStack->push($middleware);
        }
        $handlerStack->setHandler($mockHandler);

        $client = $this->clientFactory->create([
            'handler' => $handlerStack,
        ]);

        return $client->request($method, $uri, $options);
    }

    /**
     * 客户端
     *
     * @return void
     */
    protected function refreshClient(): void
    {
        $handlerStack = $this->stackFactory->create([], $this->middlewares);

        $this->client = $this->clientFactory->create([
            ...$this->config,
            'handler' => $handlerStack,
        ]);
    }

    /**
     * 添加中间件
     *
     * @param callable $middleware
     * @return $this
     */
    public function addMiddleware(callable $middleware): self
    {
        $this->middlewares[] = $middleware;
        $this->refreshClient();
        return $this;
    }

    /**
     * 设置中间件
     *
     * @param array $middlewares
     * @return $this
     */
    public function setMiddlewares(array $middlewares): self
    {
        $this->middlewares = $middlewares;
        $this->refreshClient();
        return $this;
    }
}