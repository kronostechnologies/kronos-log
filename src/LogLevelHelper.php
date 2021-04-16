<?php

namespace Kronos\Log;

use Kronos\Log\Exception\InvalidLogLevel;
use Psr\Log\LogLevel;

class LogLevelHelper
{
    public const levelPriorities = [
        LogLevel::EMERGENCY => 7,
        LogLevel::ALERT => 6,
        LogLevel::CRITICAL => 5,
        LogLevel::ERROR => 4,
        LogLevel::WARNING => 3,
        LogLevel::NOTICE => 2,
        LogLevel::INFO => 1,
        LogLevel::DEBUG => 0
    ];

    public static function isLower(string $baseLevel, string $toCompare): bool
    {
        return self::levelPriorities[$toCompare] < self::levelPriorities[$baseLevel];
    }

    public static function isHigher(string $baseLevel, string $toCompare): bool
    {
        return self::levelPriorities[$toCompare] > self::levelPriorities[$baseLevel];
    }

    /**
     * @param string $level
     * @throws InvalidLogLevel
     */
    public static function validateLogLevel(string $level): void
    {
        if (!isset(self::levelPriorities[$level])) {
            throw new InvalidLogLevel($level);
        }
    }
}
