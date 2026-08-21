<?php

namespace Kronos\Log\Factory;

use Kronos\Log\Formatter\ContextStringifier;
use Kronos\Log\Formatter\Exception\TraceBuilder;

class FormatterFactory
{

    /**
     * @return TraceBuilder
     */
    public function createExceptionTraceBuilder()
    {
        return new TraceBuilder();
    }
}
