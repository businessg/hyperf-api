<?php

namespace BusinessG\HyperfApi\Listener;

use BusinessG\HyperfApi\Annotation\ApiParam;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class ApiParamAnnotationListener implements ListenerInterface
{
    public function __construct(
        protected ConfigInterface $config
    )
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $annotations = AnnotationCollector::getClassesByAnnotation(ApiParam::class);

        foreach ($annotations as $className => $annotation) {
            /** @var ApiParam $annotation */
            $targetClass = $annotation->getApiClass();
            $apiKey = $annotation->getApiKey();

            $configKey = "business_api.apis.{$targetClass}.apis.{$apiKey}";
            $currentConfig = $this->config->get($configKey, []);

            if (is_subclass_of($className, \BusinessG\HyperfApi\ApiParam::class)) {
                $newConfig = [
                    'method' => $annotation->getMethod(),
                    'uri' => $annotation->getUri(),
                    'mock' => $annotation->isMock(),
                    'description' => $annotation->getDescription(),
                ];

                // 合并配置（注解类配置优先）
                $mergedConfig = array_merge($currentConfig, $newConfig);
                $this->config->set($configKey, new $className($mergedConfig));
            }
        }
    }
}