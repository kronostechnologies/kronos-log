<?php

namespace Kronos\Log;

use Kronos\Log\Exception\InvalidLogLevel;
use Stringable;

interface WriterInterface
{
    /**
     * @throws InvalidLogLevel
     */
    public function canLogLevel(string $level): bool;

    public function setCanLog(bool $can_log): void;

    public function canLog(): bool;

    /**
     * @throws InvalidLogLevel
     */
    public function log(string $level, string | Stringable $message, array $context = []): void;
}
