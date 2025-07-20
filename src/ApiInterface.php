<?php

namespace BusinessG\HyperfApi;

interface ApiInterface
{
    public function apis(): array;

    public function beforeRequest();

    public function request(string $apiKey, array $options = []);

    public function requestByApiParam(ApiParam $apiParam, array $options = []);

    public function batchRequest(array $apiKeys);

    public function isMock(): bool;

    public function getConfig(): array;
}