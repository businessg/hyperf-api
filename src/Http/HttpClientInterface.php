<?php

namespace BusinessG\HyperfApi\Http;

use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Event\Code\Throwable;
use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface
{
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
    ): ResponseInterface;

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
    public function batchRequest(array $requests, bool $parallel = true): array;

    public function getMiddlewares(): array;
}