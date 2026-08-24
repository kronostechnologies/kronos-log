<?php

namespace Kronos\Log\Writer;

use Kronos\Log\Traits\PrependLogLevel;
use Override;
use Stringable;

class MemoryWriter extends AbstractWriter
{
    use PrependLogLevel;

    /**
     * Contains all logged messages.
     */
    private array $_logs = [];

    public function __construct()
    {
        $this->setPrependLogLevel();
    }

    #[Override]
    public function log(string $level, string | Stringable $message, array $context = []): void
    {
        $interpolated_message = $this->interpolate($message, $context);
        $this->_logs[] = $this->prependLogLevel($level, $interpolated_message);
    }

    /**
     * Returns all logged messages.
     */
    public function getLogs(): array
    {
        return $this->_logs;
    }
}
