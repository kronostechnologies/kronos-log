<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\SyslogStrategy;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\WriterFactory;
use Kronos\Log\Writer\SyslogWriter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class SyslogStrategyTest extends \PHPUnit\Framework\TestCase
{
    const string MIN_LEVEL = 'debug';
    const string MAX_LEVEL = 'emergency';
    const string APPLICATION_VALUE = 'application value';

    private SyslogStrategy $strategy;
    private WriterFactory & MockObject $factory;
    private SyslogWriter & MockObject $writer;

    public function setUp(): void
    {
        $this->writer = $this->createMock(SyslogWriter::class);
        $this->factory = $this->createMock(WriterFactory::class);
        $this->factory->method('createSyslogWriter')->willReturn($this->writer);

        $this->strategy = new SyslogStrategy($this->factory);
    }

    #[Test]
    public function application_buildFromArray_ShouldCreateSyslogWriterWithSettings()
    {
        $this->factory
            ->expects(self::once())
            ->method('createSyslogWriter')
            ->with(self::APPLICATION_VALUE);
        $settings = $this->givenRequiredSetting();

        $this->strategy->buildFromArray($settings);
    }

    #[Test]
    public function option_buildFromArray_ShouldCreateSyslogWriterWithOption()
    {
        $this->factory
            ->expects(self::once())
            ->method('createSyslogWriter')
            ->with(self::APPLICATION_VALUE, LOG_PID);
        $settings = $this->givenRequiredSetting();
        $settings[SyslogStrategy::OPTION] = LOG_PID;

        $this->strategy->buildFromArray($settings);
    }

    #[Test]
    public function facility_buildFromArray_ShouldCreateSyslogWriterWithFacility()
    {
        $this->factory
            ->expects(self::once())
            ->method('createSyslogWriter')
            ->with(self::APPLICATION_VALUE, SyslogWriter::DEFAULT_OPTION, LOG_LOCAL6);
        $settings = $this->givenRequiredSetting();
        $settings[SyslogStrategy::FACILITY] = LOG_LOCAL6;

        $this->strategy->buildFromArray($settings);
    }

    #[Test]
    public function minLevel_buildFromArray_ShouldSetMinLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMinLevel')
            ->with(self::MIN_LEVEL);
        $settings = $this->givenRequiredSetting();
        $settings[SyslogStrategy::MIN_LEVEL] = self::MIN_LEVEL;

        $this->strategy->buildFromArray($settings);
    }

    #[Test]
    public function maxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMaxLevel')
            ->with(self::MAX_LEVEL);
        $settings = $this->givenRequiredSetting();
        $settings[SyslogStrategy::MAX_LEVEL] = self::MAX_LEVEL;

        $this->strategy->buildFromArray($settings);
    }

    #[Test]
    public function buildFromArray_ShouldReturnWriter()
    {
        $settings = $this->givenRequiredSetting();

        $actualWriter = $this->strategy->buildFromArray($settings);

        $this->assertSame($this->writer, $actualWriter);
    }

    #[Test]
    public function missingApplication_buildFromArray_ShouldThrowRequiredSettingException()
    {
        $this->expectException(RequiredSetting::class);
        $this->expectExceptionMessage(SyslogStrategy::APPLICATION . ' setting is required');

        $this->strategy->buildFromArray([]);
    }

    private function givenRequiredSetting()
    {
        return [
            SyslogStrategy::APPLICATION => self::APPLICATION_VALUE
        ];
    }
}
