<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Factory;

use Jaeger\Config;

final class JaegerStaticConfigFactory implements JaegerConfigFactory
{
    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function create(): Config
    {
        return Config::getInstance();
    }
}
