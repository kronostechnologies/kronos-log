<?php

namespace Kronos\Log\Factory\Writer;

use Kronos\Log\Enumeration\WriterTypes;
use Kronos\Log\Exception\InvalidCustomWriter;
use Kronos\Log\Exception\UnsupportedType;

class WriterFactoryProvider
{
    /**
     * @throws UnsupportedType
     */
    public function getWriterFactoryForType(?string $writerType): WriterFactory
    {
        if ($writerType === null) {
            throw new UnsupportedType('Writer type cannot be null');
        }

        $writerTypeEnum = WriterTypes::tryFrom($writerType);
        if ($writerTypeEnum !== null) {
            return match ($writerTypeEnum) {
                WriterTypes::CONSOLE => $this->createConsoleStrategy(),
                WriterTypes::FILE => $this->createFileStrategy(),
                WriterTypes::FLUENTD => $this->createFluentdStrategy(),
                WriterTypes::LOGDNA => $this->createLogDNAStrategy(),
                WriterTypes::MEMORY => $this->createMemoryStrategy(),
                WriterTypes::SENTRY => $this->createSentryStrategy(),
                WriterTypes::SYSLOG => $this->createSyslogStrategy(),
                WriterTypes::TRIGGER_ERROR => $this->createTriggerErrorStrategy(),
                default => throw new UnsupportedType("Unsupported writer type : $writerType"),
            };
        }

        if (class_exists($writerType)) {
            try {
                $customStrategy = $this->createCustomWriterStrategy();
                return $customStrategy->getStrategyForClassname($writerType);
            } catch (InvalidCustomWriter $exception) {
                throw $exception;
            } catch (\Exception $exception) {
                throw new UnsupportedType('Unsupported writer type : ' . $writerType, 0, $exception);
            }
        }

        throw new UnsupportedType('Unsupported writer type : ' . $writerType);
    }

    private function createConsoleStrategy(): ConsoleWriterFactory
    {
        return new ConsoleWriterFactory();
    }

    private function createFileStrategy(): FileWriterFactory
    {
        return new FileWriterFactory();
    }

    private function createFluentdStrategy(): FluentdWriterFactory
    {
        return new FluentdWriterFactory();
    }

    private function createLogDNAStrategy(): LogDNAWriterFactory
    {
        return new LogDNAWriterFactory();
    }

    private function createMemoryStrategy(): MemoryWriterFactory
    {
        return new MemoryWriterFactory();
    }

    private function createSentryStrategy(): SentryWriterFactory
    {
        return new SentryWriterFactory();
    }

    private function createSyslogStrategy(): SyslogWriterFactory
    {
        return new SyslogWriterFactory();
    }

    private function createTriggerErrorStrategy(): TriggerErrorWriterFactory
    {
        return new TriggerErrorWriterFactory();
    }

    private function createCustomWriterStrategy(): CustomWriterFactory
    {
        return new CustomWriterFactory();
    }
}
