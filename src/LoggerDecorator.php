<?php

namespace Kronos\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerDecorator implements LoggerInterface
{
    /**
     * @var string
     */
    private $level = LogLevel::DEBUG;
    /**
     * @var LoggerInterface
     */
    private $delegate;

    public function __construct(
        LoggerInterface $delegate
    ) {
        $this->delegate = $delegate;
    }

    public function setLevel(string $level)
    {
        $this->level = $level;
    }

    public function emergency($message, array $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        if (!LogLevelHelper::isLower($this->level, (string)$level)) {
            $this->delegate->log($level, $message, $context);
        }
    }
}
