<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Factory;

use Auxmoney\OpentracingBundle\Factory\AgentHostResolver;
use Auxmoney\OpentracingBundle\Factory\JaegerConfigFactory;
use Auxmoney\OpentracingBundle\Factory\JaegerTracerFactory;
use Exception;
use Jaeger\Config;
use Jaeger\Jaeger;
use Jaeger\Sampler\ConstSampler;
use OpenTracing\NoopTracer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use RuntimeException;

class JaegerTracerFactoryTest extends TestCase
{
    use ProphecyTrait;

    private $agentHostResolver;
    private $jaegerConfigFactory;
    private $logger;
    private string $projectName;
    private string $agentHost;
    private string $agentPort;
    private JaegerTracerFactory $subject;
    private string $samplerClass;
    private string $samplerValue;

    public function setUp(): void
    {
        parent::setUp();
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->projectName = 'project name';
        $this->agentHost = 'localhost';
        $this->agentPort = '6831';
        $this->agentHostResolver = $this->prophesize(AgentHostResolver::class);
        $this->jaegerConfigFactory = $this->prophesize(JaegerConfigFactory::class);
        $this->samplerClass = ConstSampler::class;
        $this->samplerValue = 'true';

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
        $config->setSampler(Argument::type(ConstSampler::class))->shouldBeCalled();
        $this->jaegerConfigFactory->create()->willReturn($config->reveal());

        $this->agentHostResolver->ensureAgentHostIsResolvable('localhost')->shouldBeCalled();
        $this->logger->warning(Argument::type('string'))->shouldNotBeCalled();

        self::assertSame($tracer->reveal(), $this->subject->create($this->projectName, $this->agentHost, $this->agentPort, $this->samplerClass, $this->samplerValue));
    }

    public function testCreateResolvingFailed(): void
    {
        $config = $this->prophesize(Config::class);
        $config->setSampler(Argument::type(ConstSampler::class))->shouldBeCalled();
        $this->jaegerConfigFactory->create()->willReturn($config->reveal());

        $this->agentHostResolver->ensureAgentHostIsResolvable('localhost')->shouldBeCalled()->willThrow(new RuntimeException('resolving failed'));
        $this->logger->warning(Argument::containingString('resolving failed'))->shouldBeCalledOnce();

        self::assertInstanceOf(
            NoopTracer::class,
            $this->subject->create($this->projectName, $this->agentHost, $this->agentPort, $this->samplerClass, $this->samplerValue)
        );
    }

    public function testCreateTracerInitException(): void
    {
        $config = $this->prophesize(Config::class);
        $config->initTracer('project name', Argument::type('string'))->willThrow(new Exception('tracer init exception'));
        $config->gen128bit()->shouldBeCalled();
        $config->setSampler(Argument::type(ConstSampler::class))->shouldBeCalled();
        $this->jaegerConfigFactory->create()->willReturn($config->reveal());

        $this->logger->warning(Argument::containingString('tracer init exception'))->shouldBeCalledOnce();

        self::assertInstanceOf(NoopTracer::class, $this->subject->create($this->projectName, $this->agentHost, $this->agentPort, $this->samplerClass, $this->samplerValue));
    }
}
