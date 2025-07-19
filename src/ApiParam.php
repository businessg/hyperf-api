<?php

declare(strict_types=1);

namespace BusinessG\HyperfApi;

use BusinessG\HyperfApi\Constants\HttpMethod;

class ApiParam
{
    protected string $method = HttpMethod::GET->value;
    protected string $url = '/';
    protected string $description = '';
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

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function isMock(): bool
    {
        return $this->mock;
    }

    public function setMock(bool $mock): static
    {
        $this->mock = $mock;
        return $this;
    }

    public function getMockData(): mixed
    {
        return $this->mockData;
    }

    public function setMockData(mixed $mockData): static
    {
        $this->mockData = $mockData;
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

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function setMiddlewares(array $middlewares): static
    {
        $this->middlewares = $middlewares;
        return $this;
    }
}