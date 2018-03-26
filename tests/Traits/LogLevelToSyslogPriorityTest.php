<?php

namespace Kronos\Tests\Log\Traits;

use Kronos\Log\Exception\InvalidLogLevel;
use Kronos\Log\Traits\LogLevelToSyslogPriority;
use Psr\Log\LogLevel;

class LogLevelToSyslogPriorityTest extends \PHPUnit_Framework_TestCase {

    const INVALID_LOG_LEVEL = 'invalid log level';

    /**
     * @var TestableLogLevelToSyslogPriority
     */
    private $testableTrait;

    public function setUp() {
        $this->testableTrait = new TestableLogLevelToSyslogPriority();
    }

    public function test_Emergecy_getSyslogPriorityForLogLevel_ShouldReturnLogEmerg() {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::EMERGENCY);

        $this->assertEquals(LOG_EMERG, $priority);
    }

    public function test_Alert_getSyslogPriorityForLogLevel_ShouldReturLogAlert() {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::ALERT);

        $this->assertEquals(LOG_ALERT, $priority);
    }

    public function test_Critical_getSyslogPriorityForLogLevel_ShouldReturnLogCrit() {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::CRITICAL);

        $this->assertEquals(LOG_CRIT, $priority);
    }

    public function test_Error_getSyslogPriorityForLogLevel_ShouldReturnLogErr() {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::ERROR);

        $this->assertEquals(LOG_ERR, $priority);
    }

    public function test_Warning_getSyslogPriorityForLogLevel_ShouldReturLogWarning() {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::WARNING);

        $this->assertEquals(LOG_WARNING, $priority);
    }

    public function test_Notice_getSyslogPriorityForLogLevel_ShouldReturnLogNotice() {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::NOTICE);

        $this->assertEquals(LOG_NOTICE, $priority);
    }

    public function test_Info_getSyslogPriorityForLogLevel_ShouldReturLogInfo() {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::INFO);

        $this->assertEquals(LOG_INFO, $priority);
    }

    public function test_Debug_getSyslogPriorityForLogLevel_ShouldReturnLogDebug() {
        $priority = $this->testableTrait->getSyslogPriorityForLogLevelProxy(LogLevel::DEBUG);

        $this->assertEquals(LOG_DEBUG, $priority);
    }

    public function test_LogInvalidLevel_getSyslogPriorityForLogLevel_ShouldThrowInvalidLogLevelException() {
        $this->expectException(InvalidLogLevel::class);

        $this->testableTrait->getSyslogPriorityForLogLevelProxy(self::INVALID_LOG_LEVEL);
    }
}

class TestableLogLevelToSyslogPriority {
    use LogLevelToSyslogPriority;

    /**
     * @param $level
     * @return mixed
     * @throws \Kronos\Log\Exception\InvalidLogLevel
     */
    public function getSyslogPriorityForLogLevelProxy($level) {
        return $this->getSyslogPriorityForLogLevel($level);
    }
}