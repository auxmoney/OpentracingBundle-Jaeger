<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Factory;

use Exception;
use OpenTracing\NoopTracer;
use OpenTracing\Tracer;
use Psr\Log\LoggerInterface;

final class JaegerTracerFactory implements TracerFactory
{
    private $jaegerConfigFactory;
    private $logger;

    public function __construct(
        JaegerConfigFactory $jaegerConfigFactory,
        LoggerInterface $logger
    ) {
        $this->jaegerConfigFactory = $jaegerConfigFactory;
        $this->logger = $logger;
    }

    public function create(string $projectName, string $agentHost, string $agentPort): Tracer
    {
        $tracer = new NoopTracer();

        $config = $this->jaegerConfigFactory->create();
        if (!$config) {
            $this->logger->warning(self::class . ': could not init opentracing configuration');
            return $tracer;
        }

        if (!dns_get_record($agentHost) && !filter_var($agentHost, FILTER_VALIDATE_IP)) {
            $this->logger->warning(self::class . ': could not resolve agent host "' . $agentHost . '"');
            return $tracer;
        }

        try {
            $tracer = $config->initTracer($projectName, $agentHost . ':' . $agentPort);
            $tracer->gen128bit();
        } catch (Exception $exception) {
            $this->logger->warning(self::class . ': ' . $exception->getMessage());
        }
        return $tracer;
    }
}
