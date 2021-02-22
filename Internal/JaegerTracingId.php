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
        $context = $this->tracing->injectTracingHeadersIntoCarrier([]);
        $traceHeaderName = strtoupper(Tracer_State_Header_Name);
        if (isset($context[$traceHeaderName])) {
            $fullTraceHeader = $context[$traceHeaderName];
            $delimiterPosition = strpos($fullTraceHeader, ':');
            if ($delimiterPosition !== false) {
                return substr($fullTraceHeader, 0, $delimiterPosition);
            }
        }

        return 'none';
    }
}
