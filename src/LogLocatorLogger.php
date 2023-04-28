<?php

namespace Kronos\Log;

use Stringable;
use Throwable;

class LogLocatorLogger implements LoggerInterface
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->emergency($message, $context);
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->alert($message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->critical($message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->error($message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->warning($message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->notice($message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->info($message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->debug($message, $context);
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->log($level, $message, $context);
    }

    public function exception(string $message, Throwable $exception, array $context = array()): void
    {
        LogLocator::getLogger()->exception($message, $exception, $context);
    }

    public function addContext(string $key, mixed $value): void
    {
        LogLocator::getLogger()->addContext($key, $value);
    }

    public function addContextArray(array $context): void
    {
        LogLocator::getLogger()->addContextArray($context);
    }
}
