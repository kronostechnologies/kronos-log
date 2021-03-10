<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\ExceptionTraceHelper;
use Kronos\Log\Builder\Strategy\LogDNA;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\Writer;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use PHPUnit\Framework\MockObject\MockObject;

class LogDNATest extends \PHPUnit\Framework\TestCase
{
    const MIN_LEVEL = 'debug';
    const MAX_LEVEL = 'emergency';
    const INGESTION_KEY_VALUE = 'ingestion_key';
    const APPLICATION_VALUE = 'application';
    const HOSTNAME_VALUE = 'hostname';
    const IP_ADDRESS = '127.0.0.1';
    const MAC_ADDRESS = '01:23:45:67:89:ab';

    /**
     * @var LogDNA
     */
    private $strategy;

    /**
     * @var MockObject&Writer
     */
    private $factory;

    /**
     * @var MockObject&ExceptionTraceHelper
     */
    private $exceptionTraceHelper;

    /**
     * @var MockObject&\Kronos\Log\Writer\LogDNA
     */
    private $writer;

    public function setUp(): void
    {
        $this->writer = $this->createMock(\Kronos\Log\Writer\LogDNA::class);
        $this->factory = $this->createMock(Writer::class);
        $this->factory->method('createLogDNAWriter')->willReturn($this->writer);
        $this->exceptionTraceHelper = $this->createMock(ExceptionTraceHelper::class);

        $this->strategy = new LogDNA($this->factory, $this->exceptionTraceHelper);
    }

    public function test_Settings_buildFromArray_ShouldGetExceptionTraceBuilderForSettings()
    {
        $settings = $this->givenRequiredSettings();
        $this->exceptionTraceHelper
            ->expects(self::once())
            ->method('getExceptionTraceBuilderForSettings')
            ->with($settings);

        $this->strategy->buildFromArray($settings);
    }

    public function test_Settings_buildFromArray_ShouldGetPreviousExceptionTraceBuilderForSettings()
    {
        $settings = $this->givenRequiredSettings();
        $this->exceptionTraceHelper
            ->expects(self::once())
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->with($settings);

        $this->strategy->buildFromArray($settings);
    }

    public function test_ExceptionAndPreviousExceptionTraceBuilders_buildFromArray_ShouldCreateLogDNAWriter()
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
        $this->factory
            ->expects(self::once())
            ->method('createLogDNAWriter')
            ->with(self::HOSTNAME_VALUE, self::APPLICATION_VALUE, self::INGESTION_KEY_VALUE, $exceptionTraceBuilder,
                $previousExceptionTraceBuilder);

        $this->strategy->buildFromArray($settings);
    }

    public function test_NullExceptionTraceBuilders_buildFromArray_ShouldCreateLogDNAWriter()
    {
        $settings = $this->givenRequiredSettings();
        $this->exceptionTraceHelper
            ->method('getExceptionTraceBuilderForSettings')
            ->willReturn(null);
        $this->exceptionTraceHelper
            ->method('getPreviousExceptionTraceBuilderForSettings')
            ->willReturn(null);
        $this->factory
            ->expects(self::once())
            ->method('createLogDNAWriter')
            ->with(self::HOSTNAME_VALUE, self::APPLICATION_VALUE, self::INGESTION_KEY_VALUE, null, null);

        $this->strategy->buildFromArray($settings);
    }

    public function test_MinLevel_buildFromArray_ShouldSetMinLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMinLevel')
            ->with(self::MIN_LEVEL);
        $settings = $this->givenRequiredSettings();
        $settings[LogDNA::MIN_LEVEL] = self::MIN_LEVEL;

        $this->strategy->buildFromArray($settings);
    }

    public function test_MaxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMaxLevel')
            ->with(self::MAX_LEVEL);
        $settings = $this->givenRequiredSettings();
        $settings[LogDNA::MAX_LEVEL] = self::MAX_LEVEL;

        $this->strategy->buildFromArray($settings);
    }

    public function test_IpAddress_buildFromArray_ShouldSetIpAddress()
    {
        $this->writer
            ->expects(self::once())
            ->method('setIpAddress')
            ->with(self::IP_ADDRESS);
        $settings = $this->givenRequiredSettings();
        $settings[LogDNA::IP_ADDRESS] = self::IP_ADDRESS;

        $this->strategy->buildFromArray($settings);
    }

    public function test_MacAddress_buildFromArray_ShouldSetMacAddress()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMacAddress')
            ->with(self::MAC_ADDRESS);
        $settings = $this->givenRequiredSettings();
        $settings[LogDNA::MAC_ADDRESS] = self::MAC_ADDRESS;

        $this->strategy->buildFromArray($settings);
    }

    public function test_buildFromArray_ShouldReturnWriter()
    {
        $settings = $this->givenRequiredSettings();

        $actualWriter = $this->strategy->buildFromArray($settings);

        $this->assertSame($this->writer, $actualWriter);
    }

    public function test_MissingIngestionKeySetting_buildFromArray_ShouldThrowRequiredSettingException()
    {
        $this->expectException(RequiredSetting::class);
        $this->expectExceptionMessage(LogDNA::INGESTION_KEY . ' setting is required');
        $settings = [
            LogDNA::HOSTNAME => self::HOSTNAME_VALUE,
            LogDNA::APPLICATION => self::APPLICATION_VALUE
        ];

        $this->strategy->buildFromArray($settings);
    }

    private function givenRequiredSettings()
    {
        return [
            LogDNA::HOSTNAME => self::HOSTNAME_VALUE,
            LogDNA::APPLICATION => self::APPLICATION_VALUE,
            LogDNA::INGESTION_KEY => self::INGESTION_KEY_VALUE
        ];
    }
}
