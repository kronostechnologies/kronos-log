<?php

namespace Kronos\Log;

use Throwable;

interface LoggerInterface extends \Psr\Log\LoggerInterface
{
    /**
     * Log Error with exception context
     */
    public function exception(string $message, Throwable $exception, array $context = array()): void;

    public function addContext(string $key, mixed $value): void;

    public function addContextArray(array $context): void;
}
