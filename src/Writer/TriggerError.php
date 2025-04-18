<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter;
use Override;
use Psr\Log\LogLevel;

class TriggerError extends AbstractWriter
{
    const MAP = [
        LogLevel::EMERGENCY => E_USER_WARNING,
        LogLevel::ALERT => E_USER_WARNING,
        LogLevel::CRITICAL => E_USER_WARNING,
        LogLevel::ERROR => E_USER_WARNING,
        LogLevel::WARNING => E_USER_WARNING,
        LogLevel::NOTICE => E_USER_NOTICE,
        LogLevel::INFO => E_USER_NOTICE,
        LogLevel::DEBUG => E_USER_NOTICE
    ];

    #[Override]
    public function log($level, $message, array $context = [])
    {
        trigger_error($this->interpolate($message, $context), $this->getErrorTypeFromLogLevel($level));
    }

    private function getErrorTypeFromLogLevel($level)
    {
        if (array_key_exists($level, self::MAP)) {
            return self::MAP[$level];
        }

        return E_USER_WARNING;
    }
}
