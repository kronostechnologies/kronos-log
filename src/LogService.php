<?php

namespace Kronos\Log;

use Stringable;
use Throwable;

class LogService
{
    /**
     * System is unusable.
     */
    public static function emergency(string | Stringable $message, array $context = array()): void
    {
        LogLocator::getLogger()->emergency($message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     */
    public static function alert(string | Stringable $message, array $context = array()): void
    {
        LogLocator::getLogger()->alert($message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     */
    public static function critical(string | Stringable $message, array $context = array()): void
    {
        LogLocator::getLogger()->critical($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     */
    public static function error(string | Stringable $message, array $context = array()): void
    {
        LogLocator::getLogger()->error($message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     */
    public static function warning(string | Stringable $message, array $context = array()): void
    {
        LogLocator::getLogger()->warning($message, $context);
    }

    /**
     * Normal but significant events.
     */
    public static function notice(string | Stringable $message, array $context = array()): void
    {
        LogLocator::getLogger()->notice($message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     */
    public static function info(string | Stringable $message, array $context = array()): void
    {
        LogLocator::getLogger()->info($message, $context);
    }

    /**
     * Detailed debug information.
     */
    public static function debug(string | Stringable $message, array $context = array()): void
    {
        LogLocator::getLogger()->debug($message, $context);
    }

    /**
     * Log error with exception context
     */
    public static function exception(string | Stringable $message, Throwable $exception, array $context = array()): void
    {
        $context[Logger::EXCEPTION_CONTEXT] = $exception;
        LogLocator::getLogger()->error($message, $context);
    }

    /**
     * Log warning with exception context
     */
    public static function exceptionWarning(string | Stringable $message, Throwable $exception, array $context = array()): void
    {
        $context[Logger::EXCEPTION_CONTEXT] = $exception;
        LogLocator::getLogger()->warning($message, $context);
    }
}
