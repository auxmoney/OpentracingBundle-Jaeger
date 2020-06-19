<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Functional;

use Symfony\Component\Process\Process;
use GuzzleHttp\Exception\ClientException;

class FunctionalTest extends JaegerConsoleFunctionalTest
{
    public function testSuccessfulTracing(): void
    {
        $this->setUpTestProject('default');

        $p = new Process(['symfony', 'console', 'test:jaeger'], 'build/testproject');
        $p->mustRun();
        $traceId = trim($p->getOutput());
        self::assertNotEmpty($traceId);

        $spans = $this->getSpansFromTrace($this->getTraceFromJaegerAPI($traceId));
        self::assertCount(1, $spans);
        self::assertSame('test:jaeger', $spans[0]['operationName']);
    }

    public function testSamplerConstFalseTracing(): void
    {
        $this->setUpTestProject('const-false');

        $p = new Process(['symfony', 'console', 'test:jaeger'], 'build/testproject');
        $p->mustRun();
        $traceId = trim($p->getOutput());
        self::assertNotEmpty($traceId);

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('404 Not Found');

        $this->getTraceFromJaegerAPI($traceId);
    }
}
