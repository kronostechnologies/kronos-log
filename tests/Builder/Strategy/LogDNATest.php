<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\LogDNA;
use Kronos\Log\Factory\Writer;

class LogDNATest extends \PHPUnit_Framework_TestCase {
	const MIN_LEVEL = 'debug';
	const MAX_LEVEL = 'emergency';
	const INGESTION_KEY_VALUE = 'ingestion_key';
	const APPLICATION_VALUE = 'application';
	const HOSTNAME_VALUE = 'hostname';
	const IP_ADDRESS = '127.0.0.1';
	const MAC_ADDRESS = '01:23:45:67:89:ab';

	/**
	 * @var File
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
		$this->writer = $this->getMockWithoutInvokingTheOriginalConstructor(\Kronos\Log\Writer\LogDNA::class);
		$this->factory = $this->getMock(Writer::class);
		$this->factory->method('createLogDNAWriter')->willReturn($this->writer);

		$this->strategy = new LogDNA($this->factory);
	}

	public function test_RequiredSettings_buildFromArray_ShouldCreateLogDNAWriterWithSettings() {
		$this->factory
			->expects(self::once())
			->method('createLogDNAWriter')
			->with(self::HOSTNAME_VALUE, self::APPLICATION_VALUE, self::INGESTION_KEY_VALUE);
		$settings = $this->givenRequiredSettings();

		$this->strategy->buildFromArray($settings);
	}

	public function test_MinLevel_buildFromArray_ShouldSetMinLevel() {
		$this->writer
			->expects(self::once())
			->method('setMinLevel')
			->with(self::MIN_LEVEL);
		$settings = $this->givenRequiredSettings();
		$settings[LogDNA::MIN_LEVEL] = self::MIN_LEVEL;

		$this->strategy->buildFromArray($settings);
	}

	public function test_MaxLevel_buildFromArray_ShouldSetMaxLevel() {
		$this->writer
			->expects(self::once())
			->method('setMaxLevel')
			->with(self::MAX_LEVEL);
		$settings = $this->givenRequiredSettings();
		$settings[LogDNA::MAX_LEVEL] = self::MAX_LEVEL;

		$this->strategy->buildFromArray($settings);
	}

	public function test_IpAddress_buildFromArray_ShouldSetIpAddress() {
		$this->writer
			->expects(self::once())
			->method('setIpAddress')
			->with(self::IP_ADDRESS);
		$settings = $this->givenRequiredSettings();
		$settings[LogDNA::IP_ADDRESS] = self::IP_ADDRESS;

		$this->strategy->buildFromArray($settings);
	}

	public function test_MacAddress_buildFromArray_ShouldSetMacAddress() {
		$this->writer
			->expects(self::once())
			->method('setMacAddress')
			->with(self::MAC_ADDRESS);
		$settings = $this->givenRequiredSettings();
		$settings[LogDNA::MAC_ADDRESS] = self::MAC_ADDRESS;

		$this->strategy->buildFromArray($settings);
	}

	public function test_buildFromArray_ShouldReturnWriter() {
		$settings = $this->givenRequiredSettings();

		$actualWriter = $this->strategy->buildFromArray($settings);

		$this->assertSame($this->writer, $actualWriter);
	}

	private function givenRequiredSettings() {
		return [
			LogDNA::HOSTNAME => self::HOSTNAME_VALUE,
			LogDNA::APPLICATION => self::APPLICATION_VALUE,
			LogDNA::INGESTION_KEY => self::INGESTION_KEY_VALUE
		];
	}
}