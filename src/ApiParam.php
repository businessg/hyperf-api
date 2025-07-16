<?php

namespace BusinessG\HyperfApi;

class ApiParam
{
    public string $route;
    public string $method;
    public bool $isMock;
    public bool $isProxy;
    public array $mockHandle;

}