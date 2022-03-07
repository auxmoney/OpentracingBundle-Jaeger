<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests;

use Auxmoney\OpentracingBundle\DependencyInjection\PSR18CompilerPass;
use Auxmoney\OpentracingBundle\OpentracingBundle;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OpentracingBundleTest extends TestCase
{
    use ProphecyTrait;

    private OpentracingBundle $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new OpentracingBundle();
    }

    public function testBuild(): void
    {
        $containerBuilder = $this->prophesize(ContainerBuilder::class);
        $containerBuilder->addCompilerPass(Argument::type(PSR18CompilerPass::class))->shouldBeCalled();

        $this->subject->build($containerBuilder->reveal());
    }
}
