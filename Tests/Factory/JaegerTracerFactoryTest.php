<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Factory;

use Auxmoney\OpentracingBundle\Factory\AgentHostResolver;
use Auxmoney\OpentracingBundle\Factory\JaegerConfigFactory;
use Auxmoney\OpentracingBundle\Factory\JaegerTracerFactory;
use Exception;
use Jaeger\Config;
use Jaeger\Jaeger;
use OpenTracing\NoopTracer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use RuntimeException;

class JaegerTracerFactoryTest extends TestCase
{
    private $agentHostResolver;
    private $jaegerConfigFactory;
    private $logger;
    private $projectName;
    private $agentHost;
    private $agentPort;
    private $subject;

    public function setUp()
    {
        parent::setUp();
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->projectName = 'project name';
        $this->agentHost = 'localhost';
        $this->agentPort = '6831';
        $this->agentHostResolver = $this->prophesize(AgentHostResolver::class);
        $this->jaegerConfigFactory = $this->prophesize(JaegerConfigFactory::class);

        $this->subject = new JaegerTracerFactory(
            $this->jaegerConfigFactory->reveal(),
            $this->agentHostResolver->reveal(),
            $this->logger->reveal()
        );
    }

    public function testCreateSuccess(): void
    {
        $tracer = $this->prophesize(Jaeger::class);
        $config = $this->prophesize(Config::class);
        $config->initTracer('project name', Argument::type('string'))->willReturn($tracer->reveal());
        $config->gen128bit()->shouldBeCalled();
        $this->jaegerConfigFactory->create()->willReturn($config->reveal());

        $this->agentHostResolver->ensureAgentHostIsResolvable('localhost')->shouldBeCalled();
        $this->logger->warning(Argument::type('string'))->shouldNotBeCalled();

        self::assertSame($tracer->reveal(), $this->subject->create($this->projectName, $this->agentHost, $this->agentPort));
    }

    public function testCreateResolvingFailed(): void
    {
        $config = $this->prophesize(Config::class);
        $this->jaegerConfigFactory->create()->willReturn($config->reveal());

        $this->agentHostResolver->ensureAgentHostIsResolvable('localhost')->shouldBeCalled()->willThrow(new RuntimeException('resolving failed'));
        $this->logger->warning(Argument::containingString('resolving failed'))->shouldBeCalledOnce();

        self::assertInstanceOf(
            NoopTracer::class,
            $this->subject->create($this->projectName, $this->agentHost, $this->agentPort)
        );
    }

    public function testCreateTracerInitException(): void
    {
        $config = $this->prophesize(Config::class);
        $config->initTracer('project name', Argument::type('string'))->willThrow(new Exception('tracer init exception'));
        $config->gen128bit()->shouldBeCalled();
        $this->jaegerConfigFactory->create()->willReturn($config->reveal());

        $this->logger->warning(Argument::containingString('tracer init exception'))->shouldBeCalledOnce();

        self::assertInstanceOf(NoopTracer::class, $this->subject->create($this->projectName, $this->agentHost, $this->agentPort));
    }
}
