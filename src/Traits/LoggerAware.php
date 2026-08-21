<?php

namespace Kronos\Log\Traits;

use Kronos\Log\Logger;
use Stringable;
use Throwable;
use Psr\Log\LoggerAwareTrait;

trait LoggerAware
{
    use LoggerAwareTrait;

    /**
     * System is unusable.
     */
    protected function logEmergency(string | Stringable $message, array $context = array()): void
    {
        if ($this->logger) {
            $this->logger->emergency($message, $context);
        }
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     */
    protected function logAlert(string | Stringable $message, array $context = array()): void
    {
        if ($this->logger) {
            $this->logger->alert($message, $context);
        }
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     */
    protected function logCritical(string | Stringable $message, array $context = array()): void
    {
        if ($this->logger) {
            $this->logger->critical($message, $context);
        }
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     */
    protected function logError(string | Stringable $message, array $context = array()): void
    {
        if ($this->logger) {
            $this->logger->error($message, $context);
        }
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     */
    protected function logWarning(string | Stringable $message, array $context = array()): void
    {
        if ($this->logger) {
            $this->logger->warning($message, $context);
        }
    }

    /**
     * Normal but significant events.
     */
    protected function logNotice(string | Stringable $message, array $context = array()): void
    {
        if ($this->logger) {
            $this->logger->notice($message, $context);
        }
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     */
    protected function logInfo(string | Stringable $message, array $context = array()): void
    {
        if ($this->logger) {
            $this->logger->info($message, $context);
        }
    }

    /**
     * Detailed debug information.
     */
    protected function logDebug(string | Stringable $message, array $context = array()): void
    {
        if ($this->logger) {
            $this->logger->debug($message, $context);
        }
    }

    /**
     * Log error with exception context
     */
    protected function logException(string | Stringable $message, Throwable $exception, array $context = array()): void
    {
        if ($this->logger) {
            $context[Logger::EXCEPTION_CONTEXT] = $exception;
            $this->logger->error($message, $context);
        }
    }

    /**
     * Log warning with exception context
     */
    protected function logExceptionWarning(
        string | Stringable $message,
        Throwable $exception,
        array $context = array()
    ): void {
        if ($this->logger) {
            $context[Logger::EXCEPTION_CONTEXT] = $exception;
            $this->logger->warning($message, $context);
        }
    }
}
