<?php

namespace Kronos\Tests\Log\Factory\Writer;

use GuzzleHttp\Client;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\Writer\ExceptionTraceHelper;
use Kronos\Log\Factory\Writer\LogDNAWriterFactory;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Writer\LogDNAWriter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class LogDNAWriterFactoryTest extends \PHPUnit\Framework\TestCase
{
    const string MIN_LEVEL = 'debug';
    const string MAX_LEVEL = 'emergency';
    const string INGESTION_KEY_VALUE = 'ingestion_key';
    const string APPLICATION_VALUE = 'application';
    const string HOSTNAME_VALUE = 'hostname';
    const string IP_ADDRESS = '127.0.0.1';
    const string MAC_ADDRESS = '01:23:45:67:89:ab';

    private LogDNAWriterFactory $factory;
    private ExceptionTraceHelper & MockObject $exceptionTraceHelper;

    public function setUp(): void
    {
        $this->exceptionTraceHelper = $this->createMock(ExceptionTraceHelper::class);

        $this->factory = new LogDNAWriterFactory(exceptionTraceHelper: $this->exceptionTraceHelper);
    }

    #[Test]
    public function hostnameApplicationAndIngestionKey_create_ShouldCreateLogDNAWriterWithSettings()
    {
        $writer = $this->factory->create(self::HOSTNAME_VALUE, self::APPLICATION_VALUE, self::INGESTION_KEY_VALUE);

        $this->assertSame(self::HOSTNAME_VALUE, $this->getPrivateProperty($writer, 'hostname'));
        $this->assertSame(self::APPLICATION_VALUE, $this->getPrivateProperty($writer, 'application'));
        $this->assertSame(self::INGESTION_KEY_VALUE, $this->getIngestionKey($writer));
    }

    #[Test]
    public function create_ShouldReturnLogDNAWriter()
    {
        $writer = $this->factory->create(self::HOSTNAME_VALUE, self::APPLICATION_VALUE, self::INGESTION_KEY_VALUE);

        $this->assertInstanceOf(LogDNAWriter::class, $writer);
    }

    #[Test]
    public function exceptionTraceBuilders_create_ShouldSetExceptionTraceBuildersOnWriter()
    {
        $exceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $previousExceptionTraceBuilder = $this->createMock(TraceBuilder::class);

        $writer = $this->factory->create(
            self::HOSTNAME_VALUE,
            self::APPLICATION_VALUE,
            self::INGESTION_KEY_VALUE,
            $exceptionTraceBuilder,
            $previousExceptionTraceBuilder
        );

        $this->assertSame($exceptionTraceBuilder, $this->getPrivateProperty($writer, 'exceptionTraceBuilder'));
        $this->assertSame($previousExceptionTraceBuilder, $this->getPrivateProperty($writer, 'previousExceptionTraceBuilder'));
    }

    #[Test]
    public function settings_buildFromArray_ShouldGetExceptionTraceBuilderForSettings()
    {
        $settings = $this->givenRequiredSettings();
        $this->exceptionTraceHelper
            ->expects(self::once())
            ->method('getExceptionTraceBuilderForSettings')
            ->with($settings);

        $this->factory->createFromArray($settings);
    }

    #[Test]
    public function settings_buildFromArray_ShouldGetPreviousExceptionTraceBuilderForSettings()
    {
        $settings = $this->givenRequiredSettings();
        $this->exceptionTraceHelper
            ->expects(self::once())
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->with($settings);

        $this->factory->createFromArray($settings);
    }

    #[Test]
    public function exceptionAndPreviousExceptionTraceBuilders_buildFromArray_ShouldCreateLogDNAWriterWithTraceBuilders()
    {
        $settings = $this->givenRequiredSettings();
        $exceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $this->exceptionTraceHelper
            ->method('getExceptionTraceBuilderForSettings')
            ->willReturn($exceptionTraceBuilder);
        $previousExceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $this->exceptionTraceHelper
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->willReturn($previousExceptionTraceBuilder);

        $writer = $this->factory->createFromArray($settings);

        $this->assertSame(self::HOSTNAME_VALUE, $this->getPrivateProperty($writer, 'hostname'));
        $this->assertSame(self::APPLICATION_VALUE, $this->getPrivateProperty($writer, 'application'));
        $this->assertSame(self::INGESTION_KEY_VALUE, $this->getIngestionKey($writer));
        $this->assertSame($exceptionTraceBuilder, $this->getPrivateProperty($writer, 'exceptionTraceBuilder'));
        $this->assertSame($previousExceptionTraceBuilder, $this->getPrivateProperty($writer, 'previousExceptionTraceBuilder'));
    }

    #[Test]
    public function nullExceptionTraceBuilders_buildFromArray_ShouldCreateLogDNAWriterWithoutTraceBuilders()
    {
        $settings = $this->givenRequiredSettings();
        $this->exceptionTraceHelper
            ->method('getExceptionTraceBuilderForSettings')
            ->willReturn(null);
        $this->exceptionTraceHelper
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->willReturn(null);

        $writer = $this->factory->createFromArray($settings);

        $this->assertNull($this->getPrivateProperty($writer, 'exceptionTraceBuilder'));
        $this->assertNull($this->getPrivateProperty($writer, 'previousExceptionTraceBuilder'));
    }

    #[Test]
    public function minLevel_buildFromArray_ShouldSetMinLevel()
    {
        $settings = $this->givenRequiredSettings();
        $settings[LogDNAWriterFactory::MIN_LEVEL] = self::MIN_LEVEL;

        $writer = $this->factory->createFromArray($settings);

        $this->assertSame(self::MIN_LEVEL, $this->getPrivateProperty($writer, 'min_level'));
    }

    #[Test]
    public function maxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $settings = $this->givenRequiredSettings();
        $settings[LogDNAWriterFactory::MAX_LEVEL] = self::MAX_LEVEL;

        $writer = $this->factory->createFromArray($settings);

        $this->assertSame(self::MAX_LEVEL, $this->getPrivateProperty($writer, 'max_level'));
    }

    #[Test]
    public function ipAddress_buildFromArray_ShouldSetIpAddress()
    {
        $settings = $this->givenRequiredSettings();
        $settings[LogDNAWriterFactory::IP_ADDRESS] = self::IP_ADDRESS;

        $writer = $this->factory->createFromArray($settings);

        $this->assertSame(self::IP_ADDRESS, $this->getPrivateProperty($writer, 'ip'));
    }

    #[Test]
    public function macAddress_buildFromArray_ShouldSetMacAddress()
    {
        $settings = $this->givenRequiredSettings();
        $settings[LogDNAWriterFactory::MAC_ADDRESS] = self::MAC_ADDRESS;

        $writer = $this->factory->createFromArray($settings);

        $this->assertSame(self::MAC_ADDRESS, $this->getPrivateProperty($writer, 'mac'));
    }

    #[Test]
    public function buildFromArray_ShouldReturnWriter()
    {
        $settings = $this->givenRequiredSettings();

        $actualWriter = $this->factory->createFromArray($settings);

        $this->assertInstanceOf(LogDNAWriter::class, $actualWriter);
    }

    #[Test]
    public function missingIngestionKeySetting_buildFromArray_ShouldThrowRequiredSettingException()
    {
        $this->expectException(RequiredSetting::class);
        $this->expectExceptionMessage(LogDNAWriterFactory::INGESTION_KEY . ' setting is required');
        $settings = [
            LogDNAWriterFactory::HOSTNAME => self::HOSTNAME_VALUE,
            LogDNAWriterFactory::APPLICATION => self::APPLICATION_VALUE
        ];

        $this->factory->createFromArray($settings);
    }

    private function givenRequiredSettings()
    {
        return [
            LogDNAWriterFactory::HOSTNAME => self::HOSTNAME_VALUE,
            LogDNAWriterFactory::APPLICATION => self::APPLICATION_VALUE,
            LogDNAWriterFactory::INGESTION_KEY => self::INGESTION_KEY_VALUE
        ];
    }

    private function getIngestionKey(LogDNAWriter $writer): ?string
    {
        /** @var Client $guzzleClient */
        $guzzleClient = $this->getPrivateProperty($writer, 'guzzleClient');

        return $guzzleClient->getConfig('headers')['apikey'] ?? null;
    }

    private function getPrivateProperty(object $object, string $property): mixed
    {
        $reflectionProperty = new \ReflectionProperty($object, $property);

        return $reflectionProperty->getValue($object);
    }
}
