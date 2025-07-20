<?php

namespace BusinessG\HyperfApi\Http;

use GuzzleHttp\MessageFormatter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Utils;

class SafeResponseFormatter extends MessageFormatter
{
    public function format(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?\Throwable $error = null
    ): string {
        $clonedResponse = null;
        if ($response) {
            // 保存原始流位置
            $originalPosition = $response->getBody()->tell();
            $originalContents = $response->getBody()->getContents();

            // 重置流位置
            $response->getBody()->rewind();

            // 克隆用于日志的响应
            $clonedResponse = $response->withBody(Utils::streamFor($originalContents));

            // 恢复原始流
            $response->getBody()->rewind();
            if ($originalPosition > 0) {
                $response->getBody()->seek($originalPosition);
            }
        }

        return parent::format($request, $clonedResponse ?? $response, $error);
    }
}