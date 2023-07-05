<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter;
use Kronos\Log\Exception\InvalidLogLevel;
use Kronos\Log\Logger;
use Psr\Log\LogLevel;
use Sentry\ClientInterface;
use Sentry\Severity;
use Sentry\State\Scope;
use Sentry\Tracing\PropagationContext;
use Sentry\Tracing\SpanId;
use Sentry\Tracing\TraceId;

class Sentry extends AbstractWriter
{

    const SPAN_ID = "3d1bf6350d09fb80";
    const TRACE_ID = "141bb800f59d073b7a075b1eed7d5372";

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

        if (isset($context['test'])) {
            $this->setPropagationContext($scope);
            unset($context['test']);
        }

        if (count($context)) {
            $scope->setExtras($context);
        }

        return $scope;
    }

    private function setPropagationContext(Scope $scope): void
    {
        $propagationContext = PropagationContext::fromDefaults();
        $propagationContext->setSpanId(new SpanId(self::SPAN_ID));
        $propagationContext->setTraceId(new TraceId(self::TRACE_ID));

        $scope->setPropagationContext($propagationContext);
    }

    /**
     * @throws InvalidLogLevel
     */
    private function getSentryLevelFromLogLevel($level): Severity
    {
        switch ($level) {
            case LogLevel::DEBUG:
                return Severity::debug();
            case LogLevel::WARNING:
                return Severity::warning();
            case LogLevel::ERROR:
                return Severity::error();
            case LogLevel::CRITICAL:
            case LogLevel::ALERT:
            case LogLevel::EMERGENCY:
                return Severity::fatal();
            case LogLevel::INFO:
            case LogLevel::NOTICE:
                return Severity::info();
            default:
                throw new InvalidLogLevel($level);
        }
    }
}
