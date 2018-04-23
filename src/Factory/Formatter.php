<?php

namespace Kronos\Log\Factory;

use Kronos\Log\Formatter\ContextStringifier;
use Kronos\Log\Formatter\Exception\TraceBuilder;

class Formatter
{

    /**
     * @return ContextStringifier
     */
    public function createContextStringifier()
    {
        return new ContextStringifier();
    }

    /**
     * @return TraceBuilder
     */
    public function createExceptionTraceBuilder()
    {
        return new TraceBuilder();
    }
}