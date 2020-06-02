<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Internal;

use Auxmoney\OpentracingBundle\Service\Tracing;
use const Jaeger\Constants\Tracer_State_Header_Name;

class JaegerTracingId implements TracingId
{
    private $tracing;

    public function __construct(Tracing $tracing)
    {
        $this->tracing = $tracing;
    }

    public function getAsString(): string
    {
        $carrier = [];
        $this->tracing->injectTracingHeadersIntoCarrier($carrier);

        return $carrier[Tracer_State_Header_Name];
    }
}
