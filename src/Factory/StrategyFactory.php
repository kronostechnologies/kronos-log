<?php

namespace Kronos\Log\Factory;

use Kronos\Log\Builder\Strategy\ConsoleStrategy;
use Kronos\Log\Builder\Strategy\CustomWriterStrategy;
use Kronos\Log\Builder\Strategy\FileStragegy;
use Kronos\Log\Builder\Strategy\FluentdStrategy as FluentdStrategy;
use Kronos\Log\Builder\Strategy\LogDNAStrategy;
use Kronos\Log\Builder\Strategy\MemoryStrategy;
use Kronos\Log\Builder\Strategy\Sentry;
use Kronos\Log\Builder\Strategy\SyslogStrategy;
use Kronos\Log\Builder\Strategy\TriggerErrorStrategy;

class StrategyFactory
{

    /**
     * @return ConsoleStrategy
     */
    public function createConsoleStrategy()
    {
        return new ConsoleStrategy();
    }

    /**
     * @return FileStragegy
     */
    public function createFileStrategy()
    {
        return new FileStragegy();
    }

    /**
     * @return FluentdStrategy
     */
    public function createFluentdStrategy()
    {
        return new FluentdStrategy();
    }

    /**
     * @return LogDNAStrategy
     */
    public function createLogDNAStrategy()
    {
        return new LogDNAStrategy();
    }

    /**
     * @return MemoryStrategy
     */
    public function createMemoryStrategy()
    {
        return new MemoryStrategy();
    }

    /**
     * @return Sentry
     */
    public function createSentryStrategy()
    {
        return new Sentry();
    }

    /**
     * @return SyslogStrategy
     */
    public function createSyslogStrategy()
    {
        return new SyslogStrategy();
    }

    /**
     * @return TriggerErrorStrategy
     */
    public function createTriggerErrorStrategy()
    {
        return new TriggerErrorStrategy();
    }

    /**
     * @return CustomWriterStrategy
     */
    public function createCustomWriterStrategy()
    {
        return new CustomWriterStrategy();
    }
}
