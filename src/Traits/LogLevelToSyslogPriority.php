<?php

namespace Kronos\Log\Traits;

use Kronos\Log\Exception\InvalidLogLevel;
use Psr\Log\LogLevel;

trait LogLevelToSyslogPriority {
    private $logLevelMap = [
        LogLevel::EMERGENCY => LOG_EMERG,
        LogLevel::ALERT => LOG_ALERT,
        LogLevel::CRITICAL => LOG_CRIT,
        LogLevel::ERROR => LOG_ERR,
        LogLevel::WARNING => LOG_WARNING,
        LogLevel::NOTICE => LOG_NOTICE,
        LogLevel::INFO => LOG_INFO,
        LogLevel::DEBUG => LOG_DEBUG
    ];

    /**
     * @param $level
     * @return mixed
     * @throws InvalidLogLevel
     */
    protected function getSyslogPriorityForLogLevel($level) {
        if(isset($this->logLevelMap[$level])) {
            return $this->logLevelMap[$level];
        }
        else {
            throw new InvalidLogLevel($level);
        }
    }
}