<?php

declare(strict_types=1);

namespace BusinessG\HyperfApi\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ApiParam extends AbstractAnnotation
{
    public function __construct(
        public string  $apiClass,
        public ?string $apiKey = null,
        public string  $url = '/',
        public string  $description = '',
        public string  $method = 'GET',
        public bool    $mock = false,
    )
    {
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getApiClass()
    {
        return $this->apiClass;
    }

    public function isMock(): bool
    {
        return $this->mock;
    }

    public function collectClass(string $className): void
    {
        if (empty($this->apiKey)) {
            $this->apiKey = lcfirst(basename(str_replace('\\', '/', $className)));
        }
        parent::collectClass($className);
    }
}
