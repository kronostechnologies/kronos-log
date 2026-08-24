<?php

namespace Kronos\Tests\Log\Factory\Writer;

use Kronos\Log\Factory\Writer\ConsoleWriterFactory;
use Kronos\Log\Factory\Writer\ExceptionTraceHelper;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Writer\ConsoleWriter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class ConsoleWriterFactoryTest extends \PHPUnit\Framework\TestCase
{
    const string MIN_LEVEL = 'debug';
    const string MAX_LEVEL = 'emergency';

    private ConsoleWriterFactory $factory;
    private ExceptionTraceHelper&MockObject $exceptionTraceHelper;

    public function setUp(): void
    {
        $this->exceptionTraceHelper = $this->createMock(ExceptionTraceHelper::class);

        $this->factory = new ConsoleWriterFactory(exceptionTraceHelper: $this->exceptionTraceHelper);
    }

    #[Test]
    public function create_ShouldReturnConsoleWriter()
    {
        $writer = $this->factory->create();

        $this->assertInstanceOf(ConsoleWriter::class, $writer);
    }

    #[Test]
    public function create_ShouldSetPrependDateTimeAndPrependLogLevel()
    {
        $writer = $this->factory->create();

        $this->assertTrue($this->getPrivateProperty($writer, 'prependDatetime'));
        $this->assertTrue($this->getPrivateProperty($writer, 'prepend_log_level'));
    }

    #[Test]
    public function exceptionTraceBuilders_create_ShouldSetExceptionTraceBuildersOnWriter()
    {
        $exceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $previousExceptionTraceBuilder = $this->createMock(TraceBuilder::class);

        $writer = $this->factory->create($exceptionTraceBuilder, $previousExceptionTraceBuilder);

        $this->assertSame($exceptionTraceBuilder, $this->getPrivateProperty($writer, 'exceptionTraceBuilder'));
        $this->assertSame($previousExceptionTraceBuilder, $this->getPrivateProperty($writer, 'previousExceptionTraceBuilder'));
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

        $this->factory->createFromArray($settings);
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

        $this->factory->createFromArray($settings);
    }

    #[Test]
    public function exceptionAndPreviousExceptionTraceBuilders_buildFromArray_ShouldCreateConsoleWriterWithTraceBuilders()
    {
        $exceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $this->exceptionTraceHelper
            ->method('getExceptionTraceBuilderForSettings')
            ->willReturn($exceptionTraceBuilder);
        $previousExceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $this->exceptionTraceHelper
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->willReturn($previousExceptionTraceBuilder);

        $writer = $this->factory->createFromArray([]);

        $this->assertSame($exceptionTraceBuilder, $this->getPrivateProperty($writer, 'exceptionTraceBuilder'));
        $this->assertSame($previousExceptionTraceBuilder, $this->getPrivateProperty($writer, 'previousExceptionTraceBuilder'));
    }

    #[Test]
    public function nullExceptionTraceBuilders_buildFromArray_ShouldCreateConsoleWriterWithoutTraceBuilders()
    {
        $this->exceptionTraceHelper
            ->method('getExceptionTraceBuilderForSettings')
            ->willReturn(null);
        $this->exceptionTraceHelper
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->willReturn(null);

        $writer = $this->factory->createFromArray([]);

        $this->assertNull($this->getPrivateProperty($writer, 'exceptionTraceBuilder'));
        $this->assertNull($this->getPrivateProperty($writer, 'previousExceptionTraceBuilder'));
    }

    #[Test]
    public function minLevel_buildFromArray_ShouldSetMinLevel()
    {
        $writer = $this->factory->createFromArray([ConsoleWriterFactory::MIN_LEVEL => self::MIN_LEVEL]);

        $this->assertSame(self::MIN_LEVEL, $this->getPrivateProperty($writer, 'min_level'));
    }

    #[Test]
    public function maxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $writer = $this->factory->createFromArray([ConsoleWriterFactory::MAX_LEVEL => self::MAX_LEVEL]);

        $this->assertSame(self::MAX_LEVEL, $this->getPrivateProperty($writer, 'max_level'));
    }

    #[Test]
    public function forceAnsiColor_buildFromArray_ShouldSetForceAnsiColor()
    {
        $writer = $this->factory->createFromArray([ConsoleWriterFactory::FORCE_ANSI_COLOR => true]);

        $this->assertTrue($this->getPrivateProperty($this->getPrivateProperty($writer, 'stdout'), 'force_ansi_color_support'));
        $this->assertTrue($this->getPrivateProperty($this->getPrivateProperty($writer, 'stderr'), 'force_ansi_color_support'));
    }

    #[Test]
    public function falseForceAnsiColor_buildFromArray_ShouldNotSetForceAnsiColor()
    {
        $writer = $this->factory->createFromArray([ConsoleWriterFactory::FORCE_ANSI_COLOR => false]);

        $this->assertFalse($this->getPrivateProperty($this->getPrivateProperty($writer, 'stdout'), 'force_ansi_color_support'));
        $this->assertFalse($this->getPrivateProperty($this->getPrivateProperty($writer, 'stderr'), 'force_ansi_color_support'));
    }

    #[Test]
    public function forceNoAnsiColor_buildFromArray_ShouldSetForceNoAnsiColor()
    {
        $writer = $this->factory->createFromArray([ConsoleWriterFactory::FORCE_NO_ANSI_COLOR => true]);

        $this->assertTrue($this->getPrivateProperty($this->getPrivateProperty($writer, 'stdout'), 'force_no_ansi_color_support'));
        $this->assertTrue($this->getPrivateProperty($this->getPrivateProperty($writer, 'stderr'), 'force_no_ansi_color_support'));
    }

    #[Test]
    public function falseForceNoAnsiColor_buildFromArray_ShouldNotSetForceNoAnsiColor()
    {
        $writer = $this->factory->createFromArray([ConsoleWriterFactory::FORCE_NO_ANSI_COLOR => false]);

        $this->assertFalse($this->getPrivateProperty($this->getPrivateProperty($writer, 'stdout'), 'force_no_ansi_color_support'));
        $this->assertFalse($this->getPrivateProperty($this->getPrivateProperty($writer, 'stderr'), 'force_no_ansi_color_support'));
    }

    #[Test]
    public function buildFromArray_ShouldReturnWriter()
    {
        $actualWriter = $this->factory->createFromArray([]);

        $this->assertInstanceOf(ConsoleWriter::class, $actualWriter);
    }

    private function getPrivateProperty(object $object, string $property): mixed
    {
        $reflectionProperty = new \ReflectionProperty($object, $property);

        return $reflectionProperty->getValue($object);
    }
}
