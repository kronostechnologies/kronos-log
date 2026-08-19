<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy;
use Kronos\Log\Builder\Strategy\ConsoleStrategy;
use Kronos\Log\Builder\Strategy\CustomWriterStrategy;
use Kronos\Log\Builder\Strategy\FileStragegy;
use Kronos\Log\Builder\Strategy\FluentdStrategy;
use Kronos\Log\Builder\Strategy\LogDNAStrategy;
use Kronos\Log\Builder\Strategy\MemoryStrategy;
use Kronos\Log\Builder\Strategy\StrategySelector;
use Kronos\Log\Builder\Strategy\SentryStrategy;
use Kronos\Log\Builder\Strategy\SyslogStrategy;
use Kronos\Log\Builder\Strategy\TriggerErrorStrategy;
use Kronos\Log\Enumeration\WriterTypes;
use Kronos\Log\Exception\InvalidCustomWriter;
use Kronos\Log\Exception\UnsupportedType;
use Kronos\Log\Factory\StrategyFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class StrategySelectorTest extends \PHPUnit\Framework\TestCase
{
    const string UNSUPPORTED_TYPE = 'unsupported';
    const string CUSTOM_TYPE = CustomWriterStrategy::class;

    private StrategySelector $selector;

    private StrategyFactory & MockObject $factory;


    private Strategy & MockObject $strategy;

    public function setUp(): void
    {
        $this->factory = $this->createMock(StrategyFactory::class);

        $this->selector = new StrategySelector($this->factory);
    }

    #[Test]
    public function console_getStrategyForType_ShouldCreateConsoleStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(ConsoleStrategy::class);
        $this->factory
            ->expects(self::once())
            ->method('createConsoleStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::CONSOLE->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    #[Test]
    public function fluentd_getStrategyForType_ShouldCreateFluentdStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(FluentdStrategy::class);
        $this->factory
            ->expects(self::once())
            ->method('createFluentdStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::FLUENTD->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    #[Test]
    public function file_getStrategyForType_ShouldCreateFileStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(FileStragegy::class);
        $this->factory
            ->expects(self::once())
            ->method('createFileStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::FILE->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    #[Test]
    public function logDNA_getStrategyForType_ShouldCreateLogDNAStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(LogDNAStrategy::class);
        $this->factory
            ->expects(self::once())
            ->method('createLogDNAStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::LOGDNA->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    #[Test]
    public function memory_getStrategyForType_ShouldCreateMemoryStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(MemoryStrategy::class);
        $this->factory
            ->expects(self::once())
            ->method('createMemoryStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::MEMORY->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    #[Test]
    public function sentry_getStrategyForType_ShouldCreateSentryStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(SentryStrategy::class);
        $this->factory
            ->expects(self::once())
            ->method('createSentryStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::SENTRY->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    #[Test]
    public function syslog_getStrategyForType_ShouldCreateSyslogStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(SyslogStrategy::class);
        $this->factory
            ->expects(self::once())
            ->method('createSyslogStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::SYSLOG->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    #[Test]
    public function triggerError_getStrategyForType_ShouldCreateTriggerErrorStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(TriggerErrorStrategy::class);
        $this->factory
            ->expects(self::once())
            ->method('createTriggerErrorStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::TRIGGER_ERROR->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }


    #[Test]
    public function unknownType_getStrategyForType_ShouldCreateCustomWriter()
    {
        $customWriterStrategy = $this->createMock(self::CUSTOM_TYPE);
        $this->factory
            ->expects(self::once())
            ->method('createCustomWriterStrategy')
            ->willReturn($customWriterStrategy);

        $this->selector->getStrategyForType(self::CUSTOM_TYPE);
    }

    #[Test]
    public function customWriterStrategy_getStrategyForType_ShouldGetAndReturnStrategyForType()
    {
        $strategy = $this->createMock(Strategy::class);
        $customWriterStrategy = $this->createMock(self::CUSTOM_TYPE);
        $customWriterStrategy
            ->expects(self::once())
            ->method('getStrategyForClassname')
            ->with(self::CUSTOM_TYPE)
            ->willReturn($strategy);
        $this->factory
            ->expects(self::once())
            ->method('createCustomWriterStrategy')
            ->willReturn($customWriterStrategy);

        $actualStrategy = $this->selector->getStrategyForType(self::CUSTOM_TYPE);

        $this->assertSame($strategy, $actualStrategy);
    }

    #[Test]
    public function customWriterThrowsException_getStrategyForType_ShouldThrowUnsupportedTypeException()
    {
        $this->expectException(UnsupportedType::class);
        $this->createMock(self::CUSTOM_TYPE);

        $this->selector->getStrategyForType(self::UNSUPPORTED_TYPE);
    }

    #[Test]
    public function nullType_getStrategyForType_ShouldThrowUnsupportedTypeException()
    {
        $this->expectException(UnsupportedType::class);

        $this->factory
            ->expects(self::never())
            ->method('createCustomWriterStrategy');

        $this->selector->getStrategyForType(null);
    }

    #[Test]
    public function customWriterStrategyThrowsInvalidCustomWriter_getStrategyForType_ShouldRethrowSameException()
    {
        $invalidCustomWriter = new InvalidCustomWriter('invalid writer');
        $customWriterStrategy = $this->createMock(self::CUSTOM_TYPE);
        $customWriterStrategy
            ->expects(self::once())
            ->method('getStrategyForClassname')
            ->with(self::CUSTOM_TYPE)
            ->willThrowException($invalidCustomWriter);
        $this->factory
            ->expects(self::once())
            ->method('createCustomWriterStrategy')
            ->willReturn($customWriterStrategy);

        try {
            $this->selector->getStrategyForType(self::CUSTOM_TYPE);
            $this->fail('Expected exception was not thrown');
        } catch (InvalidCustomWriter $exception) {
            $this->assertSame($invalidCustomWriter, $exception);
        }
    }

    #[Test]
    public function customWriterStrategyThrowsGenericException_getStrategyForType_ShouldThrowUnsupportedTypeExceptionWrappingIt()
    {
        $previousException = new \RuntimeException('something went wrong');
        $customWriterStrategy = $this->createMock(self::CUSTOM_TYPE);
        $customWriterStrategy
            ->expects(self::once())
            ->method('getStrategyForClassname')
            ->with(self::CUSTOM_TYPE)
            ->willThrowException($previousException);
        $this->factory
            ->expects(self::once())
            ->method('createCustomWriterStrategy')
            ->willReturn($customWriterStrategy);

        try {
            $this->selector->getStrategyForType(self::CUSTOM_TYPE);
            $this->fail('Expected exception was not thrown');
        } catch (UnsupportedType $exception) {
            $this->assertSame($previousException, $exception->getPrevious());
        }
    }

    #[Test]
    public function construct_WithoutFactory_ShouldUseDefaultStrategyFactory()
    {
        $selector = new StrategySelector();

        $strategy = $selector->getStrategyForType(WriterTypes::MEMORY->value);

        $this->assertInstanceOf(MemoryStrategy::class, $strategy);
    }
}
