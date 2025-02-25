<?php

namespace Kronos\Log;

use Override;
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

    #[Override]
    public function emergency($message, array $context = array()): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    #[Override]
    public function alert($message, array $context = array()): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    #[Override]
    public function critical($message, array $context = array()): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    #[Override]
    public function error($message, array $context = array()): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    #[Override]
    public function warning($message, array $context = array()): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    #[Override]
    public function notice($message, array $context = array()): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    #[Override]
    public function info($message, array $context = array()): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    #[Override]
    public function debug($message, array $context = array()): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    #[Override]
    public function log($level, $message, array $context = array()): void
    {
        if (!LogLevelHelper::isLower($this->level, (string)$level)) {
            $this->delegate->log($level, $message, $context);
        }
    }

    #[Override]
    public function addContext(string $key, mixed $value): void
    {
        if($this->delegate instanceof LoggerInterface) {
            $this->delegate->addContext($key, $value);
        }
    }

    #[Override]
    public function addContextArray(array $context): void
    {
        if($this->delegate instanceof LoggerInterface) {
            $this->delegate->addContextArray($context);
        }
    }

    #[Override]
    public function exception(string $message, Throwable $exception, array $context = array()): void
    {
        $context[Logger::EXCEPTION_CONTEXT] = $exception;
        $this->error($message, $context);
    }
}
