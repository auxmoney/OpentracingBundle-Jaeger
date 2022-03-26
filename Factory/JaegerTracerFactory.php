<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Factory;

use Exception;
use Jaeger\Sampler\Sampler;
use OpenTracing\NoopTracer;
use OpenTracing\Tracer;
use Psr\Log\LoggerInterface;

final class JaegerTracerFactory implements TracerFactory
{
    private JaegerConfigFactory $jaegerConfigFactory;
    private AgentHostResolver $agentHostResolver;
    private LoggerInterface $logger;

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

        $samplerValue = json_decode($samplerValue);
        /** @var Sampler $sampler */
        $sampler = new $samplerClass($samplerValue);
        $config->setSampler($sampler);
        try {
            $this->agentHostResolver->ensureAgentHostIsResolvable($agentHost);
            $config->gen128bit();
            $tracer = $config->initTracer($projectName, $agentHost . ':' . $agentPort);
        } catch (Exception $exception) {
            $this->logger->warning(self::class . ': ' . $exception->getMessage());
        }
        return $tracer;
    }
}
