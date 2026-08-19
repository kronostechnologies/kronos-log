<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\ConsoleStrategy;
use Kronos\Log\Builder\Strategy\ExceptionTraceHelper;
use Kronos\Log\Factory\WriterFactory;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class ConsoleStrategyTest extends \PHPUnit\Framework\TestCase
{
    const MIN_LEVEL = 'debug';
    const MAX_LEVEL = 'emergency';

    /**
     * @var ConsoleStrategy
     */
    private $strategy;

    /**
     * @var MockObject&WriterFactory
     */
    private $factory;

    /**
     * @var MockObject&ExceptionTraceHelper
     */
    private $exceptionTraceHelper;

    /**
     * @var MockObject&\Kronos\Log\Writer\ConsoleWriter
     */
    private $writer;

    public function setUp(): void
    {
        $this->writer = $this->createMock(\Kronos\Log\Writer\ConsoleWriter::class);
        $this->factory = $this->createMock(WriterFactory::class);
        $this->factory->method('createConsoleWriter')->willReturn($this->writer);
        $this->exceptionTraceHelper = $this->createMock(ExceptionTraceHelper::class);

        $this->strategy = new ConsoleStrategy($this->factory, $this->exceptionTraceHelper);
    }

    #[Test]
    public function settings_buildFromArray_ShouldGetExceptionTraceBuilderForSettings()
    {
        $settings = [
            'some' => 'settings',
            'details' => 'do not matter yet'
        ];
        $this->exceptionTraceHelper
            ->expects(self::once())
            ->method('getExceptionTraceBuilderForSettings')
            ->with($settings);

        $this->strategy->buildFromArray($settings);
    }

    #[Test]
    public function settings_buildFromArray_ShouldGetPreviousExceptionTraceBuilderForSettings()
    {
        $settings = [
            'some' => 'settings',
            'details' => 'do not matter yet'
        ];
        $this->exceptionTraceHelper
            ->expects(self::once())
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->with($settings);

        $this->strategy->buildFromArray($settings);
    }

    #[Test]
    public function exceptionAndPreviousExceptionTraceBuilders_buildFromArray_ShouldCreateConsoleWriter()
    {
        $exceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $this->exceptionTraceHelper
            ->method('getExceptionTraceBuilderForSettings')
            ->willReturn($exceptionTraceBuilder);
        $previousExceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $this->exceptionTraceHelper
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->willReturn($previousExceptionTraceBuilder);
        $this->factory
            ->expects(self::once())
            ->method('createConsoleWriter')
            ->with($exceptionTraceBuilder, $previousExceptionTraceBuilder);

        $this->strategy->buildFromArray([]);
    }

    #[Test]
    public function nullExceptionTraceBuilders_buildFromArray_ShouldCreateConsoleWriter()
    {
        $this->exceptionTraceHelper
            ->method('getExceptionTraceBuilderForSettings')
            ->willReturn(null);
        $this->exceptionTraceHelper
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->willReturn(null);
        $this->factory
            ->expects(self::once())
            ->method('createConsoleWriter')
            ->with(null, null);

        $this->strategy->buildFromArray([]);
    }

    #[Test]
    public function minLevel_buildFromArray_ShouldSetMinLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMinLevel')
            ->with(self::MIN_LEVEL);

        $this->strategy->buildFromArray([ConsoleStrategy::MIN_LEVEL => self::MIN_LEVEL]);
    }

    #[Test]
    public function maxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMaxLevel')
            ->with(self::MAX_LEVEL);

        $this->strategy->buildFromArray([ConsoleStrategy::MAX_LEVEL => self::MAX_LEVEL]);
    }

    #[Test]
    public function forceAnsiColor_buildFromArray_ShouldSetForceAnsiColor()
    {
        $this->writer
            ->expects(self::once())
            ->method('setForceAnsiColorSupport')
            ->with(true);

        $this->strategy->buildFromArray([ConsoleStrategy::FORCE_ANSI_COLOR => true]);
    }

    #[Test]
    public function falseForceAnsiColor_buildFromArray_ShouldNeverSetForceAnsiColor()
    {
        $this->writer
            ->expects(self::never())
            ->method('setForceAnsiColorSupport');

        $this->strategy->buildFromArray([ConsoleStrategy::FORCE_ANSI_COLOR => false]);
    }

    #[Test]
    public function forceNoAnsiColor_buildFromArray_ShouldSetForceAnsiColor()
    {
        $this->writer
            ->expects(self::once())
            ->method('setForceNoAnsiColorSupport')
            ->with(true);

        $this->strategy->buildFromArray([ConsoleStrategy::FORCE_NO_ANSI_COLOR => true]);
    }

    #[Test]
    public function falseForceNoAnsiColor_buildFromArray_ShouldNeverSetForceNoAnsiColor()
    {
        $this->writer
            ->expects(self::never())
            ->method('setForceNoAnsiColorSupport');

        $this->strategy->buildFromArray([ConsoleStrategy::FORCE_NO_ANSI_COLOR => false]);
    }

    #[Test]
    public function buildFromArray_ShouldReturnWriter()
    {
        $actualWriter = $this->strategy->buildFromArray([]);

        $this->assertSame($this->writer, $actualWriter);
    }
}
