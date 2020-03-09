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
    private $agentHostResolver;
    private $logger;

    public function __construct(
        JaegerConfigFactory $jaegerConfigFactory,
        AgentHostResolver $agentHostResolver,
        LoggerInterface $logger
    ) {
        $this->jaegerConfigFactory = $jaegerConfigFactory;
        $this->agentHostResolver = $agentHostResolver;
        $this->logger = $logger;
    }

    public function create(
        string $projectName,
        string $agentHost,
        string $agentPort,
        string $samplerClass,
        $samplerValue
    ): Tracer {
        $tracer = new NoopTracer();

        $config = $this->jaegerConfigFactory->create();
        $config->setSampler(new $samplerClass($samplerValue));
        try {
            $this->agentHostResolver->ensureAgentHostIsResolvable($agentHost);
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
