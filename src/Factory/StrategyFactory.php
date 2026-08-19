<?php

namespace Kronos\Log\Factory;

use Kronos\Log\Builder\Strategy\ConsoleStrategy;
use Kronos\Log\Builder\Strategy\CustomWriterStrategy;
use Kronos\Log\Builder\Strategy\FileStragegy;
use Kronos\Log\Builder\Strategy\FluentdStrategy;
use Kronos\Log\Builder\Strategy\LogDNAStrategy;
use Kronos\Log\Builder\Strategy\MemoryStrategy;
use Kronos\Log\Builder\Strategy\SentryStrategy;
use Kronos\Log\Builder\Strategy\SyslogStrategy;
use Kronos\Log\Builder\Strategy\TriggerErrorStrategy;

class StrategyFactory
{
    public function createConsoleStrategy(): ConsoleStrategy
    {
        return new ConsoleStrategy();
    }

    public function createFileStrategy(): FileStragegy
    {
        return new FileStragegy();
    }

    public function createFluentdStrategy(): FluentdStrategy
    {
        return new FluentdStrategy();
    }

    public function createLogDNAStrategy(): LogDNAStrategy
    {
        return new LogDNAStrategy();
    }

    public function createMemoryStrategy(): MemoryStrategy
    {
        return new MemoryStrategy();
    }

    public function createSentryStrategy(): SentryStrategy
    {
        return new SentryStrategy();
    }

    public function createSyslogStrategy(): SyslogStrategy
    {
        return new SyslogStrategy();
    }

    public function createTriggerErrorStrategy(): TriggerErrorStrategy
    {
        return new TriggerErrorStrategy();
    }

    public function createCustomWriterStrategy(): CustomWriterStrategy
    {
        return new CustomWriterStrategy();
    }
}
