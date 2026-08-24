<?php

namespace Kronos\Tests\Log\Factory\Writer;

use Kronos\Log\Enumeration\WriterTypes;
use Kronos\Log\Exception\InvalidCustomWriter;
use Kronos\Log\Exception\UnsupportedType;
use Kronos\Log\Factory\Writer\ConsoleWriterFactory;
use Kronos\Log\Factory\Writer\FileWriterFactory;
use Kronos\Log\Factory\Writer\FluentdWriterFactory;
use Kronos\Log\Factory\Writer\LogDNAWriterFactory;
use Kronos\Log\Factory\Writer\MemoryWriterFactory;
use Kronos\Log\Factory\Writer\SentryWriterFactory;
use Kronos\Log\Factory\Writer\SyslogWriterFactory;
use Kronos\Log\Factory\Writer\TriggerErrorWriterFactory;
use Kronos\Log\Factory\Writer\WriterFactory;
use Kronos\Log\Factory\Writer\WriterFactoryProvider;
use PHPUnit\Framework\Attributes\Test;

class WriterFactoryProviderTest extends \PHPUnit\Framework\TestCase
{
    const string UNSUPPORTED_TYPE = 'unsupported';
    const string CUSTOM_TYPE = MemoryWriterFactory::class;
    const string NOT_A_WRITER_FACTORY_TYPE = \stdClass::class;
    const string NOT_INSTANTIABLE_WRITER_FACTORY_TYPE = NotInstantiableWriterFactory::class;

    private WriterFactoryProvider $selector;

    public function setUp(): void
    {
        $this->selector = new WriterFactoryProvider();
    }

    #[Test]
    public function console_getStrategyForType_ShouldCreateConsoleStrategyAndReturnIt()
    {
        $actualFactory = $this->selector->getWriterFactoryForType(WriterTypes::CONSOLE->value);

        $this->assertInstanceOf(ConsoleWriterFactory::class, $actualFactory);
    }

    #[Test]
    public function fluentd_getStrategyForType_ShouldCreateFluentdStrategyAndReturnIt()
    {
        $actualFactory = $this->selector->getWriterFactoryForType(WriterTypes::FLUENTD->value);

        $this->assertInstanceOf(FluentdWriterFactory::class, $actualFactory);
    }

    #[Test]
    public function file_getStrategyForType_ShouldCreateFileStrategyAndReturnIt()
    {
        $actualFactory = $this->selector->getWriterFactoryForType(WriterTypes::FILE->value);

        $this->assertInstanceOf(FileWriterFactory::class, $actualFactory);
    }

    #[Test]
    public function logDNA_getStrategyForType_ShouldCreateLogDNAStrategyAndReturnIt()
    {
        $actualFactory = $this->selector->getWriterFactoryForType(WriterTypes::LOGDNA->value);

        $this->assertInstanceOf(LogDNAWriterFactory::class, $actualFactory);
    }

    #[Test]
    public function memory_getStrategyForType_ShouldCreateMemoryStrategyAndReturnIt()
    {
        $actualFactory = $this->selector->getWriterFactoryForType(WriterTypes::MEMORY->value);

        $this->assertInstanceOf(MemoryWriterFactory::class, $actualFactory);
    }

    #[Test]
    public function sentry_getStrategyForType_ShouldCreateSentryStrategyAndReturnIt()
    {
        $actualFactory = $this->selector->getWriterFactoryForType(WriterTypes::SENTRY->value);

        $this->assertInstanceOf(SentryWriterFactory::class, $actualFactory);
    }

    #[Test]
    public function syslog_getStrategyForType_ShouldCreateSyslogStrategyAndReturnIt()
    {
        $actualFactory = $this->selector->getWriterFactoryForType(WriterTypes::SYSLOG->value);

        $this->assertInstanceOf(SyslogWriterFactory::class, $actualFactory);
    }

    #[Test]
    public function triggerError_getStrategyForType_ShouldCreateTriggerErrorStrategyAndReturnIt()
    {
        $actualFactory = $this->selector->getWriterFactoryForType(WriterTypes::TRIGGER_ERROR->value);

        $this->assertInstanceOf(TriggerErrorWriterFactory::class, $actualFactory);
    }

    #[Test]
    public function customWriterClassname_getStrategyForType_ShouldReturnInstanceOfThatClass()
    {
        $actualFactory = $this->selector->getWriterFactoryForType(self::CUSTOM_TYPE);

        $this->assertInstanceOf(self::CUSTOM_TYPE, $actualFactory);
        $this->assertInstanceOf(WriterFactory::class, $actualFactory);
    }

    #[Test]
    public function unsupportedType_getStrategyForType_ShouldThrowUnsupportedTypeException()
    {
        $this->expectException(UnsupportedType::class);

        $this->selector->getWriterFactoryForType(self::UNSUPPORTED_TYPE);
    }

    #[Test]
    public function nullType_getStrategyForType_ShouldThrowUnsupportedTypeException()
    {
        $this->expectException(UnsupportedType::class);

        $this->selector->getWriterFactoryForType(null);
    }

    #[Test]
    public function customWriterClassNotImplementingWriterFactory_getStrategyForType_ShouldThrowInvalidCustomWriter()
    {
        $this->expectException(InvalidCustomWriter::class);

        $this->selector->getWriterFactoryForType(self::NOT_A_WRITER_FACTORY_TYPE);
    }

    #[Test]
    public function customWriterClassNotInstantiable_getStrategyForType_ShouldThrowUnsupportedTypeExceptionWrappingIt()
    {
        try {
            $this->selector->getWriterFactoryForType(self::NOT_INSTANTIABLE_WRITER_FACTORY_TYPE);
            $this->fail('Expected exception was not thrown');
        } catch (UnsupportedType $exception) {
            $this->assertNotInstanceOf(InvalidCustomWriter::class, $exception);
            $this->assertNotNull($exception->getPrevious());
        }
    }

    #[Test]
    public function construct_WithoutFactory_ShouldUseDefaultStrategyFactory()
    {
        $selector = new WriterFactoryProvider();

        $factory = $selector->getWriterFactoryForType(WriterTypes::MEMORY->value);

        $this->assertInstanceOf(MemoryWriterFactory::class, $factory);
    }
}

class NotInstantiableWriterFactory implements WriterFactory
{
    public function __construct()
    {
        throw new \RuntimeException('Cannot be instantiated');
    }

    public function createFromArray(array $settings): \Kronos\Log\Writer\WriterInterface
    {
        throw new \LogicException('Not implemented');
    }
}
