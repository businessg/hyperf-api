<?php

namespace BusinessG\HyperfApi;

interface ApiInterface
{
    public function apis(): array;

    public function getAllApis(): array;

    public function getApiParamByKey(string $apiKey): ApiParam;

    public function getApiParamByKeys(string ...$apiKeys): array;

    public function beforeRequest(ApiParam $apiParam);

    public function request(string $apiKey);

    public function requestByApiParam(ApiParam $apiParam);

    public function batchRequest(array $apiKeys);

    public function batchRequestByApiParam(array $apiParams);

    public function getConfig(): array;
}