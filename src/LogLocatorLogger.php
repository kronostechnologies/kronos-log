<?php

namespace Kronos\Log;

use Override;
use Stringable;
use Throwable;

class LogLocatorLogger implements LoggerInterface
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    #[Override]
    public function emergency(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->emergency($message, $context);
    }

    #[Override]
    public function alert(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->alert($message, $context);
    }

    #[Override]
    public function critical(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->critical($message, $context);
    }

    #[Override]
    public function error(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->error($message, $context);
    }

    #[Override]
    public function warning(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->warning($message, $context);
    }

    #[Override]
    public function notice(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->notice($message, $context);
    }

    #[Override]
    public function info(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->info($message, $context);
    }

    #[Override]
    public function debug(string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->debug($message, $context);
    }

    #[Override]
    public function log($level, string|Stringable $message, array $context = []): void
    {
        LogLocator::getLogger()->log($level, $message, $context);
    }

    #[Override]
    public function exception(string $message, Throwable $exception, array $context = array()): void
    {
        LogLocator::getLogger()->exception($message, $exception, $context);
    }

    #[Override]
    public function addContext(string $key, mixed $value): void
    {
        LogLocator::getLogger()->addContext($key, $value);
    }

    #[Override]
    public function addContextArray(array $context): void
    {
        LogLocator::getLogger()->addContextArray($context);
    }
}
