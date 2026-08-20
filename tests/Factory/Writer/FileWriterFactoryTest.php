<?php

namespace Kronos\Tests\Log\Factory\Writer;

use Kronos\Log\Adaptor\FileAdaptor;
use Kronos\Log\Adaptor\FileAdaptorFactory;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\Writer\ExceptionTraceHelper;
use Kronos\Log\Factory\Writer\FileWriterFactory;
use Kronos\Log\Formatter\ContextStringifier;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Writer\FileWriter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class FileWriterFactoryTest extends \PHPUnit\Framework\TestCase
{
    const string MIN_LEVEL = 'debug';
    const string MAX_LEVEL = 'emergency';
    const string FILENAME_VALUE = 'filename';

    private FileWriterFactory $factory;
    private FileAdaptorFactory & MockObject $fileAdaptorFactory;
    private ExceptionTraceHelper & MockObject $exceptionTraceHelper;
    private ContextStringifier & MockObject $contextStringifier;
    private FileAdaptor & MockObject $fileAdaptor;

    public function setUp(): void
    {
        $this->fileAdaptor = $this->getMockBuilder(FileAdaptor::class)->disableOriginalConstructor()->getMock();
        $this->fileAdaptorFactory = $this->createMock(FileAdaptorFactory::class);
        $this->fileAdaptorFactory->method('createFileAdaptor')->willReturn($this->fileAdaptor);
        $this->exceptionTraceHelper = $this->createMock(ExceptionTraceHelper::class);
        $this->contextStringifier = $this->createMock(ContextStringifier::class);

        $this->factory = new FileWriterFactory(
            $this->fileAdaptorFactory,
            $this->exceptionTraceHelper,
            $this->contextStringifier
        );
    }

    #[Test]
    public function filename_create_ShouldCreateFileAdaptorWithFilename()
    {
        $this->fileAdaptorFactory
            ->expects(self::once())
            ->method('createFileAdaptor')
            ->with(self::FILENAME_VALUE);

        $this->factory->create(self::FILENAME_VALUE);
    }

    #[Test]
    public function create_ShouldReturnFileWriter()
    {
        $writer = $this->factory->create(self::FILENAME_VALUE);

        $this->assertInstanceOf(FileWriter::class, $writer);
    }

    #[Test]
    public function create_ShouldSetPrependDateTimeAndPrependLogLevelAndContextStringifier()
    {
        $writer = $this->factory->create(self::FILENAME_VALUE);

        $this->assertTrue($this->getPrivateProperty($writer, 'prependDatetime'));
        $this->assertTrue($this->getPrivateProperty($writer, 'prepend_log_level'));
        $this->assertSame($this->contextStringifier, $this->getPrivateProperty($writer, 'contextStringifier'));
    }

    #[Test]
    public function exceptionTraceBuilders_create_ShouldSetExceptionTraceBuildersOnWriter()
    {
        $exceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $previousExceptionTraceBuilder = $this->createMock(TraceBuilder::class);

        $writer = $this->factory->create(self::FILENAME_VALUE, $exceptionTraceBuilder, $previousExceptionTraceBuilder);

        $this->assertSame($exceptionTraceBuilder, $this->getPrivateProperty($writer, 'exceptionTraceBuilder'));
        $this->assertSame($previousExceptionTraceBuilder, $this->getPrivateProperty($writer, 'previousExceptionTraceBuilder'));
    }

    #[Test]
    public function settings_buildFromArray_ShouldGetExceptionTraceBuilderForSettings()
    {
        $settings = [
            FileWriterFactory::FILENAME => self::FILENAME_VALUE,
            'some' => 'settings',
            'details' => 'do not matter yet'
        ];
        $this->exceptionTraceHelper
            ->expects(self::once())
            ->method('getExceptionTraceBuilderForSettings')
            ->with($settings);

        $this->factory->createFromArray($settings);
    }

    #[Test]
    public function settings_buildFromArray_ShouldGetPreviousExceptionTraceBuilderForSettings()
    {
        $settings = [
            FileWriterFactory::FILENAME => self::FILENAME_VALUE,
            'some' => 'settings',
            'details' => 'do not matter yet'
        ];
        $this->exceptionTraceHelper
            ->expects(self::once())
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->with($settings);

        $this->factory->createFromArray($settings);
    }

    #[Test]
    public function exceptionAndPreviousExceptionTraceBuilders_buildFromArray_ShouldCreateFileWriterWithTraceBuilders()
    {
        $exceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $this->exceptionTraceHelper
            ->method('getExceptionTraceBuilderForSettings')
            ->willReturn($exceptionTraceBuilder);
        $previousExceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $this->exceptionTraceHelper
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->willReturn($previousExceptionTraceBuilder);

        $writer = $this->factory->createFromArray([FileWriterFactory::FILENAME => self::FILENAME_VALUE]);

        $this->assertSame($exceptionTraceBuilder, $this->getPrivateProperty($writer, 'exceptionTraceBuilder'));
        $this->assertSame($previousExceptionTraceBuilder, $this->getPrivateProperty($writer, 'previousExceptionTraceBuilder'));
    }

    #[Test]
    public function nullExceptionTraceBuilders_buildFromArray_ShouldCreateFileWriterWithoutTraceBuilders()
    {
        $this->exceptionTraceHelper
            ->method('getExceptionTraceBuilderForSettings')
            ->willReturn(null);
        $this->exceptionTraceHelper
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->willReturn(null);

        $writer = $this->factory->createFromArray([FileWriterFactory::FILENAME => self::FILENAME_VALUE]);

        $this->assertNull($this->getPrivateProperty($writer, 'exceptionTraceBuilder'));
        $this->assertNull($this->getPrivateProperty($writer, 'previousExceptionTraceBuilder'));
    }

    #[Test]
    public function noFileName_buildFromArray_ShouldThrowRequiredException()
    {
        $this->expectException(RequiredSetting::class);
        $this->expectExceptionMessage(FileWriterFactory::FILENAME . ' setting is required');
        $this->fileAdaptorFactory
            ->expects(self::never())
            ->method('createFileAdaptor');

        $this->factory->createFromArray([]);
    }

    #[Test]
    public function minLevel_buildFromArray_ShouldSetMinLevel()
    {
        $writer = $this->factory->createFromArray([
            FileWriterFactory::FILENAME => self::FILENAME_VALUE,
            FileWriterFactory::MIN_LEVEL => self::MIN_LEVEL
        ]);

        $this->assertSame(self::MIN_LEVEL, $this->getPrivateProperty($writer, 'min_level'));
    }

    #[Test]
    public function maxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $writer = $this->factory->createFromArray([
            FileWriterFactory::FILENAME => self::FILENAME_VALUE,
            FileWriterFactory::MAX_LEVEL => self::MAX_LEVEL
        ]);

        $this->assertSame(self::MAX_LEVEL, $this->getPrivateProperty($writer, 'max_level'));
    }

    #[Test]
    public function buildFromArray_ShouldReturnFileWriter()
    {
        $writer = $this->factory->createFromArray([FileWriterFactory::FILENAME => self::FILENAME_VALUE]);

        $this->assertInstanceOf(FileWriter::class, $writer);
    }

    private function getPrivateProperty(object $object, string $property): mixed
    {
        $reflectionProperty = new \ReflectionProperty($object, $property);

        return $reflectionProperty->getValue($object);
    }
}
