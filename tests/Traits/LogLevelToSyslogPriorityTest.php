<?php

namespace Kronos\Tests\Log\Traits;

use Kronos\Log\Exception\InvalidLogLevel;
use Kronos\Log\Traits\LogLevelToSyslogPriority;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LogLevel;

class LogLevelToSyslogPriorityTest extends \PHPUnit\Framework\TestCase
{

    const INVALID_LOG_LEVEL = 'invalid log level';

    /**
     * @var TestableLogLevelToSyslogPriority
     */
    private $testableTrait;

    public function setUp(): void
    {
        $this->testableTrait = new TestableLogLevelToSyslogPriority();
    }

    #[Test]
    public function emergecy_getSyslogPriorityForLogLevel_ShouldReturnLogEmerg()
    {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::EMERGENCY);

        $this->assertEquals(LOG_EMERG, $priority);
    }

    #[Test]
    public function alert_getSyslogPriorityForLogLevel_ShouldReturLogAlert()
    {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::ALERT);

        $this->assertEquals(LOG_ALERT, $priority);
    }

    #[Test]
    public function critical_getSyslogPriorityForLogLevel_ShouldReturnLogCrit()
    {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::CRITICAL);

        $this->assertEquals(LOG_CRIT, $priority);
    }

    #[Test]
    public function error_getSyslogPriorityForLogLevel_ShouldReturnLogErr()
    {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::ERROR);

        $this->assertEquals(LOG_ERR, $priority);
    }

    #[Test]
    public function warning_getSyslogPriorityForLogLevel_ShouldReturLogWarning()
    {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::WARNING);

        $this->assertEquals(LOG_WARNING, $priority);
    }

    #[Test]
    public function notice_getSyslogPriorityForLogLevel_ShouldReturnLogNotice()
    {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::NOTICE);

        $this->assertEquals(LOG_NOTICE, $priority);
    }

    #[Test]
    public function info_getSyslogPriorityForLogLevel_ShouldReturLogInfo()
    {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::INFO);

        $this->assertEquals(LOG_INFO, $priority);
    }

    #[Test]
    public function debug_getSyslogPriorityForLogLevel_ShouldReturnLogDebug()
    {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::DEBUG);

        $this->assertEquals(LOG_DEBUG, $priority);
    }

    #[Test]
    public function logInvalidLevel_getSyslogPriorityForLogLevel_ShouldThrowInvalidLogLevelException()
    {
        $this->expectException(InvalidLogLevel::class);

        $this->testableTrait->getSyslogPriorityForLogLevelProxy(self::INVALID_LOG_LEVEL);
    }
}

class TestableLogLevelToSyslogPriority
{
    use LogLevelToSyslogPriority;

    /**
     * @param $level
     * @return mixed
     * @throws \Kronos\Log\Exception\InvalidLogLevel
     */
    public function getSyslogPriorityForLogLevelProxy($level)
    {
        return $this->getSyslogPriorityForLogLevel($level);
    }
}
