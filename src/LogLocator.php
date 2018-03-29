<?php

namespace Kronos\Log;

use Kronos\Log\Writer\TriggerError;

class LogLocator
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private static $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param bool $force
     */
    public static function setLogger(\Psr\Log\LoggerInterface $logger, $force = false)
    {
        if (!self::isLoggerSet() || $force) {
            self::$logger = $logger;
        }
    }

    /**
     * @return bool
     */
    public static function isLoggerSet()
    {
        return isset(self::$logger);
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public static function getLogger()
    {
        if (!self::isLoggerSet()) {
            self::setLogger(self::createDefaultLogger());
        }

        return self::$logger;
    }

    /**
     * @return Logger
     */
    public static function createDefaultLogger()
    {
        $logger = new Logger();
        $logger->addWriter(new TriggerError());
        return $logger;
    }
}