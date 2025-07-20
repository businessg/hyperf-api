<?php

declare(strict_types=1);

namespace BusinessG\HyperfApi;

use BusinessG\HyperfApi\Constants\ContentType;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

class ApiParam
{
    protected string $method = 'get';
    protected string $uri = '/';
    protected string $description = '';
    protected bool $mock = false;
    protected ?MockHandler $mockHandler = null;
    protected array $options = [];

    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        $this->mockHandler = $this->mockHandler ?: new MockHandler();
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): static
    {
        $this->uri = $uri;
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;
        return $this;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }


    public function enableMock(): static
    {
        $this->mock = true;
        return $this;
    }


    public function disableMock(): static
    {
        $this->mock = false;
        return $this;
    }

    public function getMockHandle(): mixed
    {
        return $this->mockHandler;
    }

    public function setMockHandle(MockHandler $mockHandler): static
    {
        $this->mockHandler = $mockHandler;
        return $this;
    }

    public function appendMockResponse(Response $response): static
    {
        $this->mockHandler->append($response);
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function buildMockResponse(
        int     $status = 200,
        array   $headers = [],
                $body = null,
        string  $version = '1.1',
        ?string $reason = null
    ): Response
    {
        return new \GuzzleHttp\Psr7\Response(
            $status,
            $headers,
            $body,
            $version,
            $reason
        );
    }

    public function buildJsonResponse(int $status = 200, array $headers = [], array $params = [])
    {
        $headers['Content-type'] = ContentType::APPLICATION_JSON->value;
        return $this->buildMockResponse($status, $headers, json_encode($params));
    }

}