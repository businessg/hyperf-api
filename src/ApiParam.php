<?php

declare(strict_types=1);

namespace BusinessG\HyperfApi;

use BusinessG\HyperfApi\Constants\HttpMethod;

class ApiParam
{
    protected HttpMethod $method = HttpMethod::GET;
    protected string $url = '/';
    protected bool $mock = false;
    protected mixed $mockData = null;
    protected array $options = [];
    protected array $middlewares = [];

    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function getMethod(): HttpMethod
    {
        return $this->method;
    }

    public function setMethod(HttpMethod $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function isMock(): bool
    {
        return $this->mock;
    }

    public function setMock(bool $mock): self
    {
        $this->mock = $mock;
        return $this;
    }

    public function getMockData(): mixed
    {
        return $this->mockData;
    }

    public function setMockData(mixed $mockData): self
    {
        $this->mockData = $mockData;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function setMiddlewares(array $middlewares): self
    {
        $this->middlewares = $middlewares;
        return $this;
    }
}