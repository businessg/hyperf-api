<?php

namespace BusinessG\HyperfApi\Listener;

use BusinessG\HyperfApi\Annotation\ApiParam;
use BusinessG\HyperfApi\Exception\BusinessApiException;
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

            if (!is_subclass_of($className, \BusinessG\HyperfApi\ApiParam::class)) {
                throw new BusinessApiException(sprintf("[%s] must be an instance of %s", $className, \BusinessG\HyperfApi\ApiParam::class));
            }

            $apiParam = new $className($apiParam ?? []);

            if ($annotation->uri) {
                $apiParam->setUri($annotation->uri);
            }
            if ($annotation->method) {
                $apiParam->setMethod($annotation->method);
            }
            if ($annotation->description) {
                $apiParam->setDescription($annotation->description);
            }

            if ($annotation->mock !== null) {
                $apiParam->setMock($annotation->mock);
            }
            $this->config->set($configKey, $apiParam);
        }
    }
}