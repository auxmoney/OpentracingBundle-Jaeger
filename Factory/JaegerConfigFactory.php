<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Factory;

use Jaeger\Config;

interface JaegerConfigFactory
{
    public function create(): Config;
}
