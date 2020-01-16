<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Tests\Factory;

use Auxmoney\OpentracingBundle\Factory\JaegerStaticConfigFactory;
use PHPUnit\Framework\TestCase;

class JaegerStaticConfigFactoryTest extends TestCase
{
    private $subject;

    public function setUp()
    {
        parent::setUp();

        $this->subject = new JaegerStaticConfigFactory();
    }

    public function testCreate(): void
    {
        $config = $this->subject->create();
        self::assertNotNull($config);
    }
}
