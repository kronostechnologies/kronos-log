<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\ConsoleStrategy;
use Kronos\Log\Builder\Strategy\CustomWriterStrategy;
use Kronos\Log\Builder\Strategy\FileStragegy;
use Kronos\Log\Builder\Strategy\LogDNAStrategy;
use Kronos\Log\Builder\Strategy\MemoryStrategy;
use Kronos\Log\Builder\Strategy\StrategySelector;
use Kronos\Log\Builder\Strategy\Sentry;
use Kronos\Log\Builder\Strategy\SyslogStrategy;
use Kronos\Log\Builder\Strategy\TriggerErrorStrategy;
use Kronos\Log\Enumeration\WriterTypes;
use Kronos\Log\Exception\UnsupportedType;
use Kronos\Log\Factory\StrategyFactory;
use PHPUnit\Framework\MockObject\MockObject;

class SelectorTest extends \PHPUnit\Framework\TestCase
{
    const string UNSUPPORTED_TYPE = 'unsupported';
    const string CUSTOM_TYPE = CustomWriterStrategy::class;

    private StrategySelector $selector;

    private StrategyFactory & MockObject $factory;


    private \Kronos\Log\Builder\Strategy & MockObject $strategy;

    public function setUp(): void
    {
        $this->factory = $this->createMock(StrategyFactory::class);

        $this->selector = new StrategySelector($this->factory);
    }

    public function test_Console_getStrategyForType_ShouldCreateConsoleStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(ConsoleStrategy::class);
        $this->factory
            ->expects(self::once())
            ->method('createConsoleStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::CONSOLE->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    public function test_File_getStrategyForType_ShouldCreateFileStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(FileStragegy::class);
        $this->factory
            ->expects(self::once())
            ->method('createFileStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::FILE->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    public function test_LogDNA_getStrategyForType_ShouldCreateLogDNAStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(LogDNAStrategy::class);
        $this->factory
            ->expects(self::once())
            ->method('createLogDNAStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::LOGDNA->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    public function test_Memory_getStrategyForType_ShouldCreateMemoryStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(MemoryStrategy::class);
        $this->factory
            ->expects(self::once())
            ->method('createMemoryStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::MEMORY->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    public function test_Sentry_getStrategyForType_ShouldCreateSentryStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(Sentry::class);
        $this->factory
            ->expects(self::once())
            ->method('createSentryStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::SENTRY->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    public function test_Syslog_getStrategyForType_ShouldCreateSyslogStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(SyslogStrategy::class);
        $this->factory
            ->expects(self::once())
            ->method('createSyslogStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::SYSLOG->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    public function test_TriggerError_getStrategyForType_ShouldCreateTriggerErrorStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(TriggerErrorStrategy::class);
        $this->factory
            ->expects(self::once())
            ->method('createTriggerErrorStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::TRIGGER_ERROR->value);

        $this->assertSame($this->strategy, $actualStrategy);
    }


    public function test_UnknownType_getStrategyForType_ShouldCreateCustomWriter()
    {
        $customWriterStrategy = $this->createMock(self::CUSTOM_TYPE);
        $this->factory
            ->expects(self::once())
            ->method('createCustomWriterStrategy')
            ->willReturn($customWriterStrategy);

        $this->selector->getStrategyForType(self::CUSTOM_TYPE);
    }

    public function test_CustomWriterStrategy_getStrategyForType_ShouldGetAndReturnStrategyForType()
    {
        $strategy = $this->createMock(\Kronos\Log\Builder\Strategy::class);
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

    public function test_CustomWriterThrowsException_getStrategyForType_ShouldThrowUnsupportedTypeException()
    {
        $this->expectException(UnsupportedType::class);
        $this->createMock(self::CUSTOM_TYPE);

        $this->selector->getStrategyForType(self::UNSUPPORTED_TYPE);
    }
}
