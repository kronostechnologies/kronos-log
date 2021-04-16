<?php

namespace Kronos\Log;

use Kronos\Log\Exception\InvalidLogLevel;
use Kronos\Log\Traits\Interpolate;
use Psr\Log\LogLevel;

abstract class AbstractWriter implements WriterInterface
{

    use Interpolate;

    protected $min_level = LogLevel::DEBUG;
    protected $max_level = LogLevel::EMERGENCY;
    protected $can_log = true;

    /**
     * @param string $level
     * @return bool
     * @throws InvalidLogLevel
     */
    public function canLogLevel($level): bool
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
     * @param string $level
     * @throws InvalidLogLevel
     */
    protected function validateLogLevel($level)
    {
        LogLevelHelper::validateLogLevel((string)$level);
    }

    /**
     * @param string $level
     * @throws InvalidLogLevel
     */
    public function setMinLevel($level)
    {
        $this->validateLogLevel($level);

        $this->min_level = $level;
    }

    /**
     * @param string $base_level
     * @param string $compared_level
     * @return bool
     */
    protected function isLevelLower($base_level, $compared_level): bool
    {
        return LogLevelHelper::isLower((string)$base_level, (string)$compared_level);
    }

    /**
     * @param string $level
     * @throws InvalidLogLevel
     */
    public function setMaxLevel($level)
    {
        $this->validateLogLevel($level);

        $this->max_level = (string)$level;
    }

    public function canLog(): bool
    {
        return $this->can_log;
    }

    /**
     * @param bool $can_log
     */
    public function setCanLog($can_log = true)
    {
        $this->can_log = (bool)$can_log;
    }

    /**
     * @param string $base_level
     * @param string $compared_level
     * @return bool
     */
    protected function isLevelHigher($base_level, $compared_level): bool
    {
        return LogLevelHelper::isHigher($base_level, $compared_level);
    }
}
