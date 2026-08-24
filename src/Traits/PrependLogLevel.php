<?php

namespace Kronos\Log\Traits;

use Stringable;

trait PrependLogLevel
{
    private bool $prepend_log_level = false;

    public function setPrependLogLevel(bool $prepend_log_level = true): void
    {
        $this->prepend_log_level = $prepend_log_level;
    }

    public function prependLogLevel(string $level, string | Stringable $message): string
    {
        if ($this->prepend_log_level) {
            return strtoupper($level) . ' : ' . (string)$message;
        } else {
            return (string)$message;
        }
    }
}
