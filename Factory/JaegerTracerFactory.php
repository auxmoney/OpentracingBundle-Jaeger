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

        if (!dns_get_record($agentHost) && !filter_var($agentHost, FILTER_VALIDATE_IP)) {
            $this->logger->warning(self::class . ': could not resolve agent host "' . $agentHost . '"');
            return $tracer;
        }

        try {
            $config->gen128bit();
            $configuredTracer = $config->initTracer($projectName, $agentHost . ':' . $agentPort);
            if ($configuredTracer) {
                $tracer = $configuredTracer;
            }
        } catch (Exception $exception) {
            $this->logger->warning(self::class . ': ' . $exception->getMessage());
        }
        return $tracer;
    }
}
