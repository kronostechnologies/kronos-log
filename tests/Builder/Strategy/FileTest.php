<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\ExceptionTraceHelper;
use Kronos\Log\Builder\Strategy\FileStragegy;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\WriterFactory;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use PHPUnit\Framework\MockObject\MockObject;

class FileTest extends \PHPUnit\Framework\TestCase
{
    const MIN_LEVEL = 'debug';
    const MAX_LEVEL = 'emergency';
    const FILENAME_VALUE = 'filename';

    /**
     * @var FileStragegy
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
     * @var MockObject&\Kronos\Log\Writer\FileWriter
     */
    private $writer;

    public function setUp(): void
    {
        $this->writer = $this->createMock(\Kronos\Log\Writer\FileWriter::class);
        $this->factory = $this->createMock(WriterFactory::class);
        $this->factory->method('createFileWriter')->willReturn($this->writer);
        $this->exceptionTraceHelper = $this->createMock(ExceptionTraceHelper::class);

        $this->strategy = new FileStragegy($this->factory, $this->exceptionTraceHelper);
    }

    public function test_Settings_buildFromArray_ShouldGetExceptionTraceBuilderForSettings()
    {
        $settings = [
            FileStragegy::FILENAME => self::FILENAME_VALUE,
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
            FileStragegy::FILENAME => self::FILENAME_VALUE,
            'some' => 'settings',
            'details' => 'do not matter yet'
        ];
        $this->exceptionTraceHelper
            ->expects(self::once())
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->with($settings);

        $this->strategy->buildFromArray($settings);
    }

    public function test_ExceptionAndPreviousExceptionTraceBuilders_buildFromArray_ShouldCreateFileWriter()
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
            ->method('createFileWriter')
            ->with(self::FILENAME_VALUE, $exceptionTraceBuilder, $previousExceptionTraceBuilder);

        $this->strategy->buildFromArray([FileStragegy::FILENAME => self::FILENAME_VALUE]);
    }

    public function test_NullExceptionTraceBuilders_buildFromArray_ShouldCreateFileWriter()
    {
        $this->exceptionTraceHelper
            ->method('getExceptionTraceBuilderForSettings')
            ->willReturn(null);
        $this->exceptionTraceHelper
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->willReturn(null);
        $this->factory
            ->expects(self::once())
            ->method('createFileWriter')
            ->with(self::FILENAME_VALUE, null, null);

        $this->strategy->buildFromArray([FileStragegy::FILENAME => self::FILENAME_VALUE]);
    }

    public function test_NoFileName_buildFromArray_ShouldThrowRequiredException()
    {
        $this->expectException(RequiredSetting::class);
        $this->expectExceptionMessage(FileStragegy::FILENAME . ' setting is required');

        $this->strategy->buildFromArray([]);
    }

    public function test_MinLevel_buildFromArray_ShouldSetMinLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMinLevel')
            ->with(self::MIN_LEVEL);

        $this->strategy->buildFromArray([FileStragegy::FILENAME => self::FILENAME_VALUE, FileStragegy::MIN_LEVEL => self::MIN_LEVEL]);
    }

    public function test_MaxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMaxLevel')
            ->with(self::MAX_LEVEL);

        $this->strategy->buildFromArray([FileStragegy::FILENAME => self::FILENAME_VALUE, FileStragegy::MAX_LEVEL => self::MAX_LEVEL]);
    }

    public function test_buildFromArray_ShouldReturnWriter()
    {
        $actualWriter = $this->strategy->buildFromArray([FileStragegy::FILENAME => self::FILENAME_VALUE]);

        $this->assertSame($this->writer, $actualWriter);
    }
}
