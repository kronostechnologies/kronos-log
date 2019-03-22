<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\Console;
use Kronos\Log\Builder\Strategy\ExceptionTraceHelper;
use Kronos\Log\Factory\Writer;
use Kronos\Log\Formatter\Exception\TraceBuilder;

class ConsoleTest extends \PHPUnit\Framework\TestCase
{
    const MIN_LEVEL = 'debug';
    const MAX_LEVEL = 'emergency';

    /**
     * @var Console
     */
    private $strategy;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $factory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $exceptionTraceHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $writer;

    public function setUp(): void
    {
        $this->writer = $this->createMock(\Kronos\Log\Writer\Console::class);
        $this->factory = $this->createMock(Writer::class);
        $this->factory->method('createConsoleWriter')->willReturn($this->writer);
        $this->exceptionTraceHelper = $this->createMock(ExceptionTraceHelper::class);

        $this->strategy = new Console($this->factory, $this->exceptionTraceHelper);
    }

    public function test_Settings_buildFromArray_ShouldGetExceptionTraceBuilderForSettings()
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

    public function test_Settings_buildFromArray_ShouldGetPreviousExceptionTraceBuilderForSettings()
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

    public function test_ExceptionAndPreviousExceptionTraceBuilders_buildFromArray_ShouldCreateConsoleWriter()
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

    public function test_NullExceptionTraceBuilders_buildFromArray_ShouldCreateConsoleWriter()
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

    public function test_MinLevel_buildFromArray_ShouldSetMinLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMinLevel')
            ->with(self::MIN_LEVEL);

        $this->strategy->buildFromArray([Console::MIN_LEVEL => self::MIN_LEVEL]);
    }

    public function test_MaxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMaxLevel')
            ->with(self::MAX_LEVEL);

        $this->strategy->buildFromArray([Console::MAX_LEVEL => self::MAX_LEVEL]);
    }

    public function test_ForceAnsiColor_buildFromArray_ShouldSetForceAnsiColor()
    {
        $this->writer
            ->expects(self::once())
            ->method('setForceAnsiColorSupport')
            ->with(true);

        $this->strategy->buildFromArray([Console::FORCE_ANSI_COLOR => true]);
    }

    public function test_FalseForceAnsiColor_buildFromArray_ShouldNeverSetForceAnsiColor()
    {
        $this->writer
            ->expects(self::never())
            ->method('setForceAnsiColorSupport');

        $this->strategy->buildFromArray([Console::FORCE_ANSI_COLOR => false]);
    }

    public function test_ForceNoAnsiColor_buildFromArray_ShouldSetForceAnsiColor()
    {
        $this->writer
            ->expects(self::once())
            ->method('setForceNoAnsiColorSupport')
            ->with(true);

        $this->strategy->buildFromArray([Console::FORCE_NO_ANSI_COLOR => true]);
    }

    public function test_FalseForceNoAnsiColor_buildFromArray_ShouldNeverSetForceNoAnsiColor()
    {
        $this->writer
            ->expects(self::never())
            ->method('setForceNoAnsiColorSupport');

        $this->strategy->buildFromArray([Console::FORCE_NO_ANSI_COLOR => false]);
    }

    public function test_buildFromArray_ShouldReturnWriter()
    {
        $actualWriter = $this->strategy->buildFromArray([]);

        $this->assertSame($this->writer, $actualWriter);
    }
}
