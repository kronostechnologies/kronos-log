<?php

namespace Kronos\Log\Factory;

use Kronos\Log\Builder\Strategy\Console;
use Kronos\Log\Builder\Strategy\CustomWriter;
use Kronos\Log\Builder\Strategy\File;
use Kronos\Log\Builder\Strategy\Graylog as GraylogStrategy;
use Kronos\Log\Builder\Strategy\Fluentd as FluentdStrategy;
use Kronos\Log\Builder\Strategy\LogDNA;
use Kronos\Log\Builder\Strategy\Memory;
use Kronos\Log\Builder\Strategy\Sentry;
use Kronos\Log\Builder\Strategy\Syslog;
use Kronos\Log\Builder\Strategy\TriggerError;
use Kronos\Log\Builder\Strategy\WebSocket;

class Strategy
{

    /**
     * @return Console
     */
    public function createConsoleStrategy()
    {
        return new Console();
    }

    /**
     * @return File
     */
    public function createFileStrategy()
    {
        return new File();
    }

    /**
     * @return GraylogStrategy
     */
    public function createGraylogStrategy()
    {
        return new GraylogStrategy();
    }

    /**
     * @return FluentdStrategy
     */
    public function createFluentdStrategy()
    {
        return new FluentdStrategy();
    }

    /**
     * @return LogDNA
     */
    public function createLogDNAStrategy()
    {
        return new LogDNA();
    }

    /**
     * @return Memory
     */
    public function createMemoryStrategy()
    {
        return new Memory();
    }

    /**
     * @return Sentry
     */
    public function createSentryStrategy()
    {
        return new Sentry();
    }

    /**
     * @return Syslog
     */
    public function createSyslogStrategy()
    {
        return new Syslog();
    }

    /**
     * @return TriggerError
     */
    public function createTriggerErrorStrategy()
    {
        return new TriggerError();
    }

    /**
     * @return WebSocket
     */
    public function createWebsocketStrategy()
    {
        return new WebSocket();
    }

    /**
     * @return CustomWriter
     */
    public function createCustomWriterStrategy()
    {
        return new CustomWriter();
    }
}
