<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\File;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\Writer;

class FileTest extends \PHPUnit_Framework_TestCase {
	const MIN_LEVEL = 'debug';
	const MAX_LEVEL = 'emergency';
	const FILENAME_VALUE = 'filename';

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
		$this->writer = $this->getMockWithoutInvokingTheOriginalConstructor(\Kronos\Log\Writer\File::class);
		$this->factory = $this->getMock(Writer::class);
		$this->factory->method('createFileWriter')->willReturn($this->writer);

		$this->strategy = new File($this->factory);
	}

	public function test_Filename_buildFromArray_ShouldCreateFileWriterWithFilename() {
		$this->factory
			->expects(self::once())
			->method('createFileWriter')
			->with(self::FILENAME_VALUE);

		$this->strategy->buildFromArray([File::FILENAME => self::FILENAME_VALUE]);
	}

	public function test_MinLevel_buildFromArray_ShouldSetMinLevel() {
		$this->writer
			->expects(self::once())
			->method('setMinLevel')
			->with(self::MIN_LEVEL);

		$this->strategy->buildFromArray([File::FILENAME => self::FILENAME_VALUE, File::MIN_LEVEL => self::MIN_LEVEL]);
	}

	public function test_MaxLevel_buildFromArray_ShouldSetMaxLevel() {
		$this->writer
			->expects(self::once())
			->method('setMaxLevel')
			->with(self::MAX_LEVEL);

		$this->strategy->buildFromArray([File::FILENAME => self::FILENAME_VALUE, File::MAX_LEVEL => self::MAX_LEVEL]);
	}

	public function test_buildFromArray_ShouldReturnWriter() {
		$actualWriter = $this->strategy->buildFromArray([File::FILENAME => self::FILENAME_VALUE]);

		$this->assertSame($this->writer, $actualWriter);
	}

	public function test_NoFileName_buildFromArray_ShouldThrowRequiredException() {
		$this->expectException(RequiredSetting::class);
		$this->expectExceptionMessage(File::FILENAME.' setting is required');

		$this->strategy->buildFromArray([]);
	}
}