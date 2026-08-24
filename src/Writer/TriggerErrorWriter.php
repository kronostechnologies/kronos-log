<?php

namespace Kronos\Log\Writer;

use Override;
use Psr\Log\LogLevel;
use Stringable;

class TriggerErrorWriter extends AbstractWriter
{
    /**
     * @var array<string, int>
     */
    const array MAP = [
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
    public function log(string $level, string | Stringable $message, array $context = []): void
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        trigger_error($this->interpolate($message, $context), $this->getErrorTypeFromLogLevel($level));
    }

    private function getErrorTypeFromLogLevel($level): int
    {
        if (array_key_exists($level, self::MAP)) {
            return self::MAP[$level];
        }

        return E_USER_WARNING;
    }
}
