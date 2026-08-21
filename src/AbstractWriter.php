<?php

namespace Kronos\Log;

use Kronos\Log\Exception\InvalidLogLevel;
use Kronos\Log\Traits\Interpolate;
use Override;
use Psr\Log\LogLevel;

abstract class AbstractWriter implements WriterInterface
{
    use Interpolate;

    protected string $min_level = LogLevel::DEBUG;
    protected string $max_level = LogLevel::EMERGENCY;
    protected bool $can_log = true;

    #[Override]
    public function canLogLevel(string $level): bool
    {
        $this->validateLogLevel($level);

        if (!$this->can_log
            || $this->isLevelLower($this->min_level, $level)
            || $this->isLevelHigher($this->max_level, $level)) {
            return false;
        }

        return true;
    }

    /**
     * @throws InvalidLogLevel
     */
    protected function validateLogLevel(string $level): void
    {
        LogLevelHelper::validateLogLevel($level);
    }

    /**
     * @throws InvalidLogLevel
     */
    public function setMinLevel(string $level): void
    {
        $this->validateLogLevel($level);

        $this->min_level = $level;
    }

    /**
     * @param string $base_level
     * @param string $compared_level
     * @return bool
     */
    protected function isLevelLower(string $base_level, string $compared_level): bool
    {
        return LogLevelHelper::isLower($base_level, $compared_level);
    }

    /**
     * @param string $level
     * @throws InvalidLogLevel
     */
    public function setMaxLevel(string $level): void
    {
        $this->validateLogLevel($level);
        $this->max_level = $level;
    }

    #[Override]
    public function canLog(): bool
    {
        return $this->can_log;
    }

    /**
     * @param bool $can_log
     */
    #[Override]
    public function setCanLog(bool $can_log = true): void
    {
        $this->can_log = $can_log;
    }

    /**
     * @param string $base_level
     * @param string $compared_level
     * @return bool
     */
    protected function isLevelHigher(string $base_level, string $compared_level): bool
    {
        return LogLevelHelper::isHigher($base_level, $compared_level);
    }
}
