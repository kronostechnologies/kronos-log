<?php

namespace Kronos\Log;

use Kronos\Log\Writer\TriggerError;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class LogLocator
{
    private static ?LoggerInterface $logger = null;

    public static function setLogger(LoggerInterface|PsrLoggerInterface $logger, bool $force = false): void
    {
        if (!$logger instanceof LoggerInterface) {
            $logger = new LoggerDecorator($logger);
        }

        if (!self::isLoggerSet() || $force) {
            self::$logger = $logger;
        }
    }

    public static function isLoggerSet(): bool
    {
        return self::$logger !== null;
    }

    public static function getLogger(): LoggerInterface
    {
        if (!self::isLoggerSet()) {
            self::setLogger(self::createDefaultLogger());
        }

        /** @psalm-var LoggerInterface self::$logger */
        return self::$logger;
    }

    public static function unsetLogger(): void
    {
        self::$logger = null;
    }

    public static function createDefaultLogger(): Logger
    {
        $logger = new Logger();
        $logger->addWriter(new TriggerError());
        return $logger;
    }
}
