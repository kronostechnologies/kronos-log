<?php

use Kronos\Log\Exception\InvalidLogLevel;
use Kronos\Log\LogLevelHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LogLevelHelperTest extends TestCase
{
    #[Test]
    public function isLower_shouldReturnFalseWhenLevelIsEqual(): void
    {
        $isLower = LogLevelHelper::isLower(LogLevel::DEBUG, LogLevel::DEBUG);

        self::assertFalse($isLower);
    }

    #[DataProvider('provideLevels')]
    #[Test]
    public function isLower_shouldReturnFalseWhenLevelIsHigher($baseLevel, $toCompare): void
    {
        $isLower = LogLevelHelper::isLower($baseLevel, $toCompare);

        self::assertFalse($isLower);
    }

    #[Test]
    public function validateLogLevel_shouldThrowInvalidLogLevelWhenLevelIsUnknown(): void
    {
        self::expectException(InvalidLogLevel::class);

        LogLevelHelper::validateLogLevel('unknown level');
    }

    public static function provideLevels(): array
    {
        return [
            [LogLevel::ALERT, LogLevel::EMERGENCY],
            [LogLevel::CRITICAL, LogLevel::ALERT],
            [LogLevel::ERROR, LogLevel::CRITICAL],
            [LogLevel::WARNING, LogLevel::ERROR],
            [LogLevel::NOTICE, LogLevel::WARNING],
            [LogLevel::INFO, LogLevel::NOTICE],
            [LogLevel::DEBUG, LogLevel::INFO],
        ];
    }
}
