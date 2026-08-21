<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter;
use Kronos\Log\Exception\InvalidLogLevel;
use Kronos\Log\Logger;
use Override;
use Psr\Log\LogLevel;
use Sentry\ClientInterface;
use Sentry\Severity;
use Sentry\State\Scope;
use Stringable;

class SentryWriter extends AbstractWriter
{
    public function __construct(
        private readonly ?ClientInterface $sentryClient
    ) {
    }

    #[Override]
    public function log(string $level, string | Stringable $message, array $context = []): void
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

    private function captureMessage(Severity $level, string | Stringable $message, array $context): void
    {
        $interpolatedMessage = $this->interpolate($message, $context);
        $sentryScope = $this->getSentryScope($level, $context);

        if (isset($this->sentryClient)) {
            $this->sentryClient->captureMessage($interpolatedMessage, $level, $sentryScope);
        }
    }

    private function captureException(Severity $level, string | Stringable $message, array $context): void
    {
        $exception = $context[Logger::EXCEPTION_CONTEXT];
        unset($context[Logger::EXCEPTION_CONTEXT]);

        if ($message) {
            $context['loggerMessage'] = $message;
        }

        $sentryScope = $this->getSentryScope($level, $context);
        if (isset($this->sentryClient)) {
            $this->sentryClient->captureException($exception, $sentryScope);
        }
    }

    protected function getSentryScope($level, $context): Scope
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
    private function getSentryLevelFromLogLevel($level): Severity
    {
        return match ($level) {
            LogLevel::DEBUG => Severity::debug(),
            LogLevel::WARNING => Severity::warning(),
            LogLevel::ERROR => Severity::error(),
            LogLevel::CRITICAL, LogLevel::ALERT, LogLevel::EMERGENCY => Severity::fatal(),
            LogLevel::INFO, LogLevel::NOTICE => Severity::info(),
            default => throw new InvalidLogLevel($level),
        };
    }
}
