<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\Memory;
use Kronos\Log\Factory\Writer;

class MemoryTest extends \PHPUnit_Framework_TestCase {
	const MIN_LEVEL = 'debug';
	const MAX_LEVEL = 'emergency';

	/**
	 * @var Memory
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
		$this->writer = $this->getMockWithoutInvokingTheOriginalConstructor(\Kronos\Log\Writer\Memory::class);
		$this->factory = $this->getMock(Writer::class);
		$this->factory->method('createMemoryWriter')->willReturn($this->writer);

		$this->strategy = new Memory($this->factory);
	}

	public function test_buildFromArray_ShouldCreateMemoryWriter() {
		$this->factory
			->expects(self::once())
			->method('createMemoryWriter');

		$this->strategy->buildFromArray([]);
	}

	public function test_MinLevel_buildFromArray_ShouldSetMinLevel() {
		$this->writer
			->expects(self::once())
			->method('setMinLevel')
			->with(self::MIN_LEVEL);

		$this->strategy->buildFromArray([Memory::MIN_LEVEL => self::MIN_LEVEL]);
	}

	public function test_MaxLevel_buildFromArray_ShouldSetMaxLevel() {
		$this->writer
			->expects(self::once())
			->method('setMaxLevel')
			->with(self::MAX_LEVEL);

		$this->strategy->buildFromArray([Memory::MAX_LEVEL => self::MAX_LEVEL]);
	}

	public function test_buildFromArray_ShouldReturnWriter() {
		$actualWriter = $this->strategy->buildFromArray([]);

		$this->assertSame($this->writer, $actualWriter);
	}
}