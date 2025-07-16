<?php

namespace BusinessG\HyperfApi;

interface ApiInterface
{
    public function apis(): array;

    public function middlewares(): array;

    public function beforeRequest();

    public function request(string $apiKey, ApiParam $apiParam);

    public function batchRequest(array $apiKeys);

    public function isMock(): bool;

    public function getConfig(): array;
}