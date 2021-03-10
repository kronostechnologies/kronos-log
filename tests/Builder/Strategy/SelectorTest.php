<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\Console;
use Kronos\Log\Builder\Strategy\CustomWriter;
use Kronos\Log\Builder\Strategy\File;
use Kronos\Log\Builder\Strategy\LogDNA;
use Kronos\Log\Builder\Strategy\Memory;
use Kronos\Log\Builder\Strategy\Selector;
use Kronos\Log\Builder\Strategy\Syslog;
use Kronos\Log\Builder\Strategy\TriggerError;
use Kronos\Log\Enumeration\WriterTypes;
use Kronos\Log\Exception\InvalidCustomWriter;
use Kronos\Log\Exception\UnsupportedType;
use Kronos\Log\Factory\Strategy;
use Kronos\Log\Writer\Sentry;
use PHPUnit\Framework\MockObject\MockObject;

class SelectorTest extends \PHPUnit\Framework\TestCase
{
    const UNSUPPORTED_TYPE = 'unsupported';
    const CUSTOM_TYPE = 'custom type';

    /**
     * @var Selector
     */
    private $selector;

    /**
     * @var MockObject&Strategy
     */
    private $factory;

    /**
     * @var MockObject&Console
     */
    private $strategy;

    public function setUp(): void
    {
        $this->factory = $this->createMock(Strategy::class);

        $this->selector = new Selector($this->factory);
    }

    public function test_Console_getStrategyForType_ShouldCreateConsoleStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(Console::class);
        $this->factory
            ->expects(self::once())
            ->method('createConsoleStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::CONSOLE);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    public function test_File_getStrategyForType_ShouldCreateFileStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(File::class);
        $this->factory
            ->expects(self::once())
            ->method('createFileStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::FILE);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    public function test_LogDNA_getStrategyForType_ShouldCreateLogDNAStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(LogDNA::class);
        $this->factory
            ->expects(self::once())
            ->method('createLogDNAStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::LOGDNA);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    public function test_Memory_getStrategyForType_ShouldCreateMemoryStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(Memory::class);
        $this->factory
            ->expects(self::once())
            ->method('createMemoryStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::MEMORY);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    public function test_Sentry_getStrategyForType_ShouldCreateSentryStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(Sentry::class);
        $this->factory
            ->expects(self::once())
            ->method('createSentryStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::SENTRY);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    public function test_Syslog_getStrategyForType_ShouldCreateSyslogStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(Syslog::class);
        $this->factory
            ->expects(self::once())
            ->method('createSyslogStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::SYSLOG);

        $this->assertSame($this->strategy, $actualStrategy);
    }

    public function test_TriggerError_getStrategyForType_ShouldCreateTriggerErrorStrategyAndReturnIt()
    {
        $this->strategy = $this->createMock(TriggerError::class);
        $this->factory
            ->expects(self::once())
            ->method('createTriggerErrorStrategy')
            ->willReturn($this->strategy);

        $actualStrategy = $this->selector->getStrategyForType(WriterTypes::TRIGGER_ERROR);

        $this->assertSame($this->strategy, $actualStrategy);
    }


    public function test_UnknownType_getStrategyForType_ShouldCreateCustomWriter()
    {
        $customWriterStrategy = $this->createMock(CustomWriter::class);
        $this->factory
            ->expects(self::once())
            ->method('createCustomWriterStrategy')
            ->willReturn($customWriterStrategy);

        $this->selector->getStrategyForType(self::CUSTOM_TYPE);
    }

    public function test_CustomWriterStrategy_getStrategyForType_ShouldGetAndReturnStrategyForType()
    {
        $strategy = $this->createMock(\Kronos\Log\Builder\Strategy::class);
        $customWriterStrategy = $this->createMock(CustomWriter::class);
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
        $customWriterStrategy = $this->createMock(CustomWriter::class);
        $customWriterStrategy
            ->expects(self::once())
            ->method('getStrategyForClassname')
            ->with(self::UNSUPPORTED_TYPE)
            ->willThrowException(new \Exception());
        $this->factory
            ->expects(self::once())
            ->method('createCustomWriterStrategy')
            ->willReturn($customWriterStrategy);

        $this->selector->getStrategyForType(self::UNSUPPORTED_TYPE);
    }

    public function test_InvalidCustomWriter_getStrategyForType_ShouldThrowInvalidCustomWriterException()
    {
        $this->expectException(InvalidCustomWriter::class);
        $customWriterStrategy = $this->createMock(CustomWriter::class);
        $customWriterStrategy
            ->expects(self::once())
            ->method('getStrategyForClassname')
            ->with(self::UNSUPPORTED_TYPE)
            ->willThrowException(new InvalidCustomWriter());
        $this->factory
            ->expects(self::once())
            ->method('createCustomWriterStrategy')
            ->willReturn($customWriterStrategy);

        $this->selector->getStrategyForType(self::UNSUPPORTED_TYPE);
    }
}
