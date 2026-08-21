<?php

namespace Kronos\Tests\Log;

use Kronos\Log\Exception\InvalidLogLevel;
use \Psr\Log\LogLevel;
use PHPUnit\Framework\Attributes\Test;
use Stringable;

class AbstractWriterTest extends \PHPUnit\Framework\TestCase
{

    const string ANY_LEVEL = LogLevel::INFO;
    const string INVALID_LOG_LEVEL = 'invalid';

    const string LOWER_LEVEL = LogLevel::NOTICE;
    const string HIGHER_LEVEL = LogLevel::CRITICAL;

    private TestableWriter $writer;

    public function setUp(): void
    {
        $this->writer = new TestableWriter();
    }

    #[Test]
    public function newWriter_CanLogLevel_ShouldReturnTrue()
    {
        $canLog = $this->writer->canLogLevel(self::ANY_LEVEL);

        $this->assertTrue($canLog);
    }

    #[Test]
    public function writer_CanLogLevel_WhenCanLogIsFalse_ShouldReturnFalse()
    {
        $this->writer->setCanLog(false);

        $canLog = $this->writer->canLogLevel(self::ANY_LEVEL);

        $this->assertFalse($canLog);
    }

    #[Test]
    public function newWriter_CanLogLevelWithInvalidLevel_ShouldThrowInvalidLogLevelException()
    {
        $this->expectException(InvalidLogLevel::class);

        $this->writer->canLogLevel(self::INVALID_LOG_LEVEL);
    }

    #[Test]
    public function writerWithMinLevel_CanLogLevelWithLowerLevel_ShouldReturnFalse()
    {
        $this->writer->setMinLevel(self::HIGHER_LEVEL);

        $canLog = $this->writer->canLogLevel(self::LOWER_LEVEL);

        $this->assertFalse($canLog);
    }

    #[Test]
    public function writerWithMinLevel_CanLogLevelWithHigerLevel_ShouldReturnTrue()
    {
        $this->writer->setMinLevel(self::LOWER_LEVEL);

        $canLog = $this->writer->canLogLevel(self::HIGHER_LEVEL);

        $this->assertTrue($canLog);
    }

    #[Test]
    public function newWriter_SetMinLevelWithInvalidLevel_ShouldThrowInvalidLogLevelException()
    {
        $this->expectException(InvalidLogLevel::class);

        $this->writer->setMinLevel(self::INVALID_LOG_LEVEL);
    }

    #[Test]
    public function writerWithMaxLevel_CanLogLevelWithHigherLevel_SouldReturnFalse()
    {
        $this->writer->setMaxLevel(self::LOWER_LEVEL);

        $canLog = $this->writer->canLogLevel(self::HIGHER_LEVEL);

        $this->assertFalse($canLog);
    }

    #[Test]
    public function writerWithMaxLevel_CanLogLevelWithLowerLevel_SouldReturnTrue()
    {
        $this->writer->setMaxLevel(self::HIGHER_LEVEL);

        $canLog = $this->writer->canLogLevel(self::LOWER_LEVEL);

        $this->assertTrue($canLog);
    }

    #[Test]
    public function newWriter_SetMaxLevelWithInvalidLevel_ShouldThrowInvalidLogLevelException()
    {
        $this->expectException(InvalidLogLevel::class);

        $this->writer->setMaxLevel(self::INVALID_LOG_LEVEL);
    }
}

class TestableWriter extends \Kronos\Log\AbstractWriter
{
    public function log(string $level, string | Stringable $message, array $context = []): void
    {
    }
}
