<?php

use Kronos\Log\Exception\InvalidLogLevel;
use Kronos\Log\LogLevelHelper;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LogLevelHelperTest extends TestCase
{
    public function test_isLower_shouldReturnFalseWhenLevelIsEqual(): void
    {
        $isLower = LogLevelHelper::isLower(LogLevel::DEBUG, LogLevel::DEBUG);

        self::assertFalse($isLower);
    }

    /**
     * @dataProvider provideLevels
     */
    public function test_isLower_shouldReturnFalseWhenLevelIsHigher($baseLevel, $toCompare): void
    {
        $isLower = LogLevelHelper::isLower($baseLevel, $toCompare);

        self::assertFalse($isLower);
    }

    public function test_validateLogLevel_shouldThrowInvalidLogLevelWhenLevelIsUnknown(): void
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
