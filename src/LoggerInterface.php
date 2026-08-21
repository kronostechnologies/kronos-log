<?php

namespace Kronos\Log;

use Stringable;
use Throwable;

interface LoggerInterface extends \Psr\Log\LoggerInterface
{
    /**
     * Log error  with exception context
     */
    public function exception(string|Stringable $message, Throwable $exception, array $context = array()): void;

    /**
     * Log warning with exception context
     */
    public function exceptionWarning(string|Stringable $message, Throwable $exception, array $context = array()): void;

    public function addContext(string $key, mixed $value): void;

    public function addContextArray(array $context): void;
}
