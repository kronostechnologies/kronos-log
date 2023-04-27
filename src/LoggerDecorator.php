<?php

namespace Kronos\Log;

use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

class LoggerDecorator implements LoggerInterface
{
    private string $level = LogLevel::DEBUG;

    private PsrLoggerInterface $delegate;

    public function __construct(
        LoggerInterface|PsrLoggerInterface $delegate
    ) {
        $this->delegate = $delegate;
    }

    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    public function emergency($message, array $context = array()): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = array()): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = array()): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = array()): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = array()): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = array()): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = array()): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = array()): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = array()): void
    {
        if (!LogLevelHelper::isLower($this->level, (string)$level)) {
            $this->delegate->log($level, $message, $context);
        }
    }

    public function addContext(string $key, mixed $value): void
    {
        if($this->delegate instanceof LoggerInterface) {
            $this->delegate->addContext($key, $value);
        }
    }

    public function addContextArray(array $context): void
    {
        if($this->delegate instanceof LoggerInterface) {
            $this->delegate->addContextArray($context);
        }
    }

    public function exception(string $message, Throwable $exception, array $context = array()): void
    {
        $context[Logger::EXCEPTION_CONTEXT] = $exception;
        $this->error($message, $context);
    }
}
