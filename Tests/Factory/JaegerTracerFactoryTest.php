<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Factory;

use Auxmoney\OpentracingBundle\Factory\JaegerConfigFactory;
use Auxmoney\OpentracingBundle\Factory\JaegerTracerFactory;
use Exception;
use Jaeger\Config;
use Jaeger\Jaeger;
use OpenTracing\NoopTracer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class JaegerTracerFactoryTest extends TestCase
{
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
        $this->jaegerConfigFactory = $this->prophesize(JaegerConfigFactory::class);

        $this->subject = new JaegerTracerFactory(
            $this->jaegerConfigFactory->reveal(),
            $this->logger->reveal()
        );
    }

    public function testCreateSuccessDns(): void
    {
        $tracer = $this->prophesize(Jaeger::class);
        $config = $this->prophesize(Config::class);
        $config->initTracer('project name', Argument::type('string'))->willReturn($tracer->reveal());
        $this->jaegerConfigFactory->create()->willReturn($config->reveal());

        $this->logger->warning(Argument::type('string'))->shouldNotBeCalled();

        self::assertSame($tracer->reveal(), $this->subject->create($this->projectName, $this->agentHost, $this->agentPort));
    }

    public function testCreateSuccessIp(): void
    {
        $this->subject = new JaegerTracerFactory(
            $this->jaegerConfigFactory->reveal(),
            $this->logger->reveal()
        );

        $tracer = $this->prophesize(Jaeger::class);
        $config = $this->prophesize(Config::class);
        $config->initTracer('project name', Argument::type('string'))->willReturn($tracer->reveal());
        $this->jaegerConfigFactory->create()->willReturn($config->reveal());

        $this->logger->warning(Argument::type('string'))->shouldNotBeCalled();

        self::assertSame($tracer->reveal(), $this->subject->create($this->projectName, '127.0.0.1', $this->agentPort));
    }

    public function testCreateNoConfig(): void
    {
        $this->jaegerConfigFactory->create()->shouldBeCalled()->willReturn(null);

        $this->logger->warning(Argument::containingString('could not init opentracing configuration'))->shouldBeCalledOnce();

        self::assertInstanceOf(NoopTracer::class, $this->subject->create($this->projectName, $this->agentHost, $this->agentPort));
    }

    public function testCreateNoDnsOrIp(): void
    {
        $this->subject = new JaegerTracerFactory(
            $this->jaegerConfigFactory->reveal(),
            $this->logger->reveal()
        );

        $config = $this->prophesize(Config::class);
        $this->jaegerConfigFactory->create()->willReturn($config->reveal());

        $this->logger->warning(Argument::containingString('could not resolve agent host'))->shouldBeCalledOnce();

        self::assertInstanceOf(
            NoopTracer::class,
            $this->subject->create($this->projectName, 'älsakfdkaofkeäkvaäsooäaegölsgälkfdvpaoskvä.cöm', $this->agentPort)
        );
    }

    public function testCreateTracerInitException(): void
    {
        $config = $this->prophesize(Config::class);
        $config->initTracer('project name', Argument::type('string'))->willThrow(new Exception('tracer init exception'));
        $this->jaegerConfigFactory->create()->willReturn($config->reveal());

        $this->logger->warning(Argument::containingString('tracer init exception'))->shouldBeCalledOnce();

        self::assertInstanceOf(NoopTracer::class, $this->subject->create($this->projectName, $this->agentHost, $this->agentPort));
    }
}
