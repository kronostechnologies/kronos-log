<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Enumeration\WriterTypes;
use Kronos\Log\Exception\InvalidCustomWriter;
use Kronos\Log\Exception\UnsupportedType;
use Kronos\Log\Factory\StrategyFactory;
use Kronos\Log\Factory\WriterFactory;

class StrategySelector
{
    private StrategyFactory $factory;

    public function __construct(?StrategyFactory $factory = null)
    {
        $this->factory = $factory ?: new StrategyFactory();
    }

    /**
     * @throws UnsupportedType
     */
    public function getStrategyForType(?string $writerType): \Kronos\Log\Builder\Strategy
    {
        if ($writerType === null) {
            throw new UnsupportedType('Writer type cannot be null');
        }

        $writerTypeEnum = WriterTypes::tryFrom($writerType);
        if ($writerTypeEnum !== null) {
            return match ($writerTypeEnum) {
                WriterTypes::CONSOLE => $this->factory->createConsoleStrategy(),
                WriterTypes::FILE => $this->factory->createFileStrategy(),
                WriterTypes::FLUENTD => $this->factory->createFluentdStrategy(),
                WriterTypes::LOGDNA => $this->factory->createLogDNAStrategy(),
                WriterTypes::MEMORY => $this->factory->createMemoryStrategy(),
                WriterTypes::SENTRY => $this->factory->createSentryStrategy(),
                WriterTypes::SYSLOG => $this->factory->createSyslogStrategy(),
                WriterTypes::TRIGGER_ERROR => $this->factory->createTriggerErrorStrategy(),
                default => throw new UnsupportedType("Unsupported writer type : $writerType"),
            };
        }

        if (class_exists($writerType)) {
            try {
                $customStrategy = $this->factory->createCustomWriterStrategy();
                return $customStrategy->getStrategyForClassname($writerType);
            } catch (InvalidCustomWriter $exception) {
                throw $exception;
            } catch (\Exception $exception) {
                throw new UnsupportedType('Unsupported writer type : ' . $writerType, 0, $exception);
            }
        }

        throw new UnsupportedType('Unsupported writer type : ' . $writerType);
    }
}
