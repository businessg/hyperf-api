<?php

namespace BusinessG\HyperfApi;

interface ApiInterface
{
    public function apis();

    public function middlewares();

    public function beforeRequest();

    public function request(string $apiKey, object $apiParam);

    public function batchRequest(array $apiKeys);

    public function getConfig(): array;
}