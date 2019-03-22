<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter;
use Kronos\Log\Exception\InvalidLogLevel;
use Kronos\Log\Logger;
use Psr\Log\LogLevel;

class Sentry extends AbstractWriter
{

    private $log_level_map = [
        LogLevel::EMERGENCY => \Raven_Client::FATAL,
        LogLevel::ALERT => \Raven_Client::FATAL,
        LogLevel::CRITICAL => \Raven_Client::ERROR,
        LogLevel::ERROR => \Raven_Client::ERROR,
        LogLevel::WARNING => \Raven_Client::WARNING,
        LogLevel::NOTICE => \Raven_Client::INFO,
        LogLevel::INFO => \Raven_Client::INFO,
        LogLevel::DEBUG => \Raven_Client::DEBUG
    ];

    /**
     * @var \Raven_Client
     */
    private $raven_client;

    /**
     * Sentry constructor.
     * @param \Raven_Client $raven_client
     */
    public function __construct(\Raven_Client $raven_client)
    {
        $this->raven_client = $raven_client;
    }

    public function log($level, $message, array $context = [])
    {
        if ($this->hasExceptionInContext($context)) {
            $this->captureException($level, $message, $context);
        } else {
            $this->captureMessage($level, $message, $context);
        }
    }

    private function hasExceptionInContext($context)
    {
        return isset($context[Logger::EXCEPTION_CONTEXT]);
    }

    private function captureMessage($level, $message, $context)
    {
        $interpolated_message = $this->interpolate($message, $context);
        $sentry_params = $this->getSentryParams($level, $context);

        $this->raven_client->captureMessage($interpolated_message, [], $sentry_params);
    }

    private function captureException($level, $message, $context)
    {
        $exception = $context[Logger::EXCEPTION_CONTEXT];
        unset($context[Logger::EXCEPTION_CONTEXT]);

        if ($message) {
            $context['loggerMessage'] = $message;
        }

        $sentry_params = $this->getSentryParams($level, $context);
        $this->raven_client->captureException($exception, $sentry_params);
    }

    private function getSentryParams($level, $context)
    {
        $sentry_params = [
            'level' => $this->getSentryLevelFromLogLevel($level)
        ];

        if (count($context)) {
            $sentry_params['extra'] = $context;
        }

        return $sentry_params;
    }

    private function getSentryLevelFromLogLevel($level)
    {
        if (isset($this->log_level_map[$level])) {
            return $this->log_level_map[$level];
        } else {
            throw new InvalidLogLevel($level);
        }
    }

}
