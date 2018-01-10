<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\Syslog;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\Writer;

class SyslogTest extends \PHPUnit_Framework_TestCase {
	const MIN_LEVEL = 'debug';
	const MAX_LEVEL = 'emergency';

	const APPLICATION_VALUE = 'application value';

	/**
	 * @var Syslog
	 */
	private $strategy;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $factory;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $writer;

	public function setUp() {
		$this->writer = $this->getMockWithoutInvokingTheOriginalConstructor(\Kronos\Log\Writer\Syslog::class);
		$this->factory = $this->getMock(Writer::class);
		$this->factory->method('createSyslogWriter')->willReturn($this->writer);

		$this->strategy = new Syslog($this->factory);
	}

	public function test_Application_buildFromArray_ShouldCreateSyslogWriterWithSettings() {
		$this->factory
			->expects(self::once())
			->method('createSyslogWriter')
			->with(self::APPLICATION_VALUE);
		$settings = $this->givenRequiredSetting();

		$this->strategy->buildFromArray($settings);
	}

	public function test_Option_buildFromArray_ShouldCreateSyslogWriterWithOption() {
		$this->factory
			->expects(self::once())
			->method('createSyslogWriter')
			->with(self::APPLICATION_VALUE, LOG_PID);
		$settings = $this->givenRequiredSetting();
		$settings[Syslog::OPTION] = LOG_PID;

		$this->strategy->buildFromArray($settings);
	}

	public function test_Facility_buildFromArray_ShouldCreateSyslogWriterWithFacility() {
		$this->factory
			->expects(self::once())
			->method('createSyslogWriter')
			->with(self::APPLICATION_VALUE, \Kronos\Log\Writer\Syslog::DEFAULT_OPTION, LOG_LOCAL6);
		$settings = $this->givenRequiredSetting();
		$settings[Syslog::FACILITY] = LOG_LOCAL6;

		$this->strategy->buildFromArray($settings);
	}

	public function test_MinLevel_buildFromArray_ShouldSetMinLevel() {
		$this->writer
			->expects(self::once())
			->method('setMinLevel')
			->with(self::MIN_LEVEL);
		$settings = $this->givenRequiredSetting();
		$settings[Syslog::MIN_LEVEL] = self::MIN_LEVEL;

		$this->strategy->buildFromArray($settings);
	}

	public function test_MaxLevel_buildFromArray_ShouldSetMaxLevel() {
		$this->writer
			->expects(self::once())
			->method('setMaxLevel')
			->with(self::MAX_LEVEL);
		$settings = $this->givenRequiredSetting();
		$settings[Syslog::MAX_LEVEL] = self::MAX_LEVEL;

		$this->strategy->buildFromArray($settings);
	}

	public function test_buildFromArray_ShouldReturnWriter() {
		$settings = $this->givenRequiredSetting();

		$actualWriter = $this->strategy->buildFromArray($settings);

		$this->assertSame($this->writer, $actualWriter);
	}

	public function test_MissingApplication_buildFromArray_ShouldThrowRequiredSettingException() {
		$this->expectException(RequiredSetting::class);
		$this->expectExceptionMessage(Syslog::APPLICATION.' setting is required');

		$this->strategy->buildFromArray([]);
	}

	private function givenRequiredSetting() {
		return [
			Syslog::APPLICATION => self::APPLICATION_VALUE
		];
	}
}