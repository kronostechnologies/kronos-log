<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter;
use Kronos\Log\Exception\InvalidLogLevel;
use Kronos\Log\Logger;
use Psr\Log\LogLevel;
use Sentry\Client;
use Sentry\ClientInterface;
use Sentry\Severity;
use Sentry\State\Scope;

class Sentry extends AbstractWriter
{

    private $logLevelMap = [
        LogLevel::EMERGENCY => Severity::FATAL,
        LogLevel::ALERT => Severity::FATAL,
        LogLevel::CRITICAL => Severity::ERROR,
        LogLevel::ERROR => Severity::ERROR,
        LogLevel::WARNING => Severity::WARNING,
        LogLevel::NOTICE => Severity::INFO,
        LogLevel::INFO => Severity::INFO,
        LogLevel::DEBUG => Severity::DEBUG
    ];

    /**
     * @var ClientInterface
     */
    private $sentryClient;

    /**
     * Sentry constructor.
     */
    public function __construct(ClientInterface $sentryClient)
    {
        $this->sentryClient = $sentryClient;
    }

    /**
     * @throws InvalidLogLevel
     */
    public function log($level, $message, array $context = [])
    {
        $level = $this->getSentryLevelFromLogLevel($level);
        if ($this->hasExceptionInContext($context)) {
            $this->captureException($level, $message, $context);
        } else {
            $this->captureMessage($level, $message, $context);
        }
    }

    private function hasExceptionInContext($context): bool
    {
        return isset($context[Logger::EXCEPTION_CONTEXT]);
    }

    private function captureMessage($level, $message, $context)
    {
        $interpolatedMessage = $this->interpolate($message, $context);
        $sentryScope = $this->getSentryScope($level, $context);

        $this->sentryClient->captureMessage($interpolatedMessage, $level, $sentryScope);
    }

    private function captureException($level, $message, $context)
    {
        $exception = $context[Logger::EXCEPTION_CONTEXT];
        unset($context[Logger::EXCEPTION_CONTEXT]);

        if ($message) {
            $context['loggerMessage'] = $message;
        }

        $sentryScope = $this->getSentryScope($level, $context);
        $this->sentryClient->captureException($exception, $sentryScope);
    }

    private function getSentryScope($level, $context): Scope
    {
        $scope = new Scope();
        $scope->setLevel($level);
        if (count($context)) {
            $scope->setExtras($context);
        }

        return $scope;
    }

    /**
     * @throws InvalidLogLevel
     */
    private function getSentryLevelFromLogLevel($level): string
    {
        if (isset($this->logLevelMap[$level])) {
            return $this->logLevelMap[$level];
        } else {
            throw new InvalidLogLevel($level);
        }
    }
}
