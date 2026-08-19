<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\SyslogStrategy;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\WriterFactory;
use PHPUnit\Framework\MockObject\MockObject;

class SyslogTest extends \PHPUnit\Framework\TestCase
{
    const MIN_LEVEL = 'debug';
    const MAX_LEVEL = 'emergency';

    const APPLICATION_VALUE = 'application value';

    /**
     * @var SyslogStrategy
     */
    private $strategy;

    /**
     * @var MockObject&WriterFactory
     */
    private $factory;

    /**
     * @var MockObject&\Kronos\Log\Writer\SyslogWriter
     */
    private $writer;

    public function setUp(): void
    {
        $this->writer = $this->createMock(\Kronos\Log\Writer\SyslogWriter::class);
        $this->factory = $this->createMock(WriterFactory::class);
        $this->factory->method('createSyslogWriter')->willReturn($this->writer);

        $this->strategy = new SyslogStrategy($this->factory);
    }

    public function test_Application_buildFromArray_ShouldCreateSyslogWriterWithSettings()
    {
        $this->factory
            ->expects(self::once())
            ->method('createSyslogWriter')
            ->with(self::APPLICATION_VALUE);
        $settings = $this->givenRequiredSetting();

        $this->strategy->buildFromArray($settings);
    }

    public function test_Option_buildFromArray_ShouldCreateSyslogWriterWithOption()
    {
        $this->factory
            ->expects(self::once())
            ->method('createSyslogWriter')
            ->with(self::APPLICATION_VALUE, LOG_PID);
        $settings = $this->givenRequiredSetting();
        $settings[SyslogStrategy::OPTION] = LOG_PID;

        $this->strategy->buildFromArray($settings);
    }

    public function test_Facility_buildFromArray_ShouldCreateSyslogWriterWithFacility()
    {
        $this->factory
            ->expects(self::once())
            ->method('createSyslogWriter')
            ->with(self::APPLICATION_VALUE, \Kronos\Log\Writer\SyslogWriter::DEFAULT_OPTION, LOG_LOCAL6);
        $settings = $this->givenRequiredSetting();
        $settings[SyslogStrategy::FACILITY] = LOG_LOCAL6;

        $this->strategy->buildFromArray($settings);
    }

    public function test_MinLevel_buildFromArray_ShouldSetMinLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMinLevel')
            ->with(self::MIN_LEVEL);
        $settings = $this->givenRequiredSetting();
        $settings[SyslogStrategy::MIN_LEVEL] = self::MIN_LEVEL;

        $this->strategy->buildFromArray($settings);
    }

    public function test_MaxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMaxLevel')
            ->with(self::MAX_LEVEL);
        $settings = $this->givenRequiredSetting();
        $settings[SyslogStrategy::MAX_LEVEL] = self::MAX_LEVEL;

        $this->strategy->buildFromArray($settings);
    }

    public function test_buildFromArray_ShouldReturnWriter()
    {
        $settings = $this->givenRequiredSetting();

        $actualWriter = $this->strategy->buildFromArray($settings);

        $this->assertSame($this->writer, $actualWriter);
    }

    public function test_MissingApplication_buildFromArray_ShouldThrowRequiredSettingException()
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
