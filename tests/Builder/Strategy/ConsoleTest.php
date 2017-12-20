<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\Console;
use Kronos\Log\Factory\Writer;

class ConsoleTest extends \PHPUnit_Framework_TestCase {
	const MIN_LEVEL = 'debug';
	const MAX_LEVEL = 'emergency';

	/**
	 * @var Console
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

	const SOME_CONTEXT = 1; // APP

	public function setUp() {
		$this->writer = $this->getMockWithoutInvokingTheOriginalConstructor(\Kronos\Log\Writer\Console::class);
		$this->factory = $this->getMock(Writer::class);
		$this->factory->method('createConsoleWriter')->willReturn($this->writer);

		$this->strategy = new Console($this->factory);
	}

	public function test_buildFromArray_ShouldCreateConsoleWriter() {
		$this->factory
			->expects(self::once())
			->method('createConsoleWriter');

		$this->strategy->buildFromArray([], self::SOME_CONTEXT);
	}

	public function test_MinLevel_buildFromArray_ShouldSetMinLevel() {
		$this->writer
			->expects(self::once())
			->method('setMinLevel')
			->with(self::MIN_LEVEL);

		$this->strategy->buildFromArray([Console::MIN_LEVEL => self::MIN_LEVEL], self::SOME_CONTEXT);
	}

	public function test_MaxLevel_buildFromArray_ShouldSetMaxLevel() {
		$this->writer
			->expects(self::once())
			->method('setMaxLevel')
			->with(self::MAX_LEVEL);

		$this->strategy->buildFromArray([Console::MAX_LEVEL => self::MAX_LEVEL], self::SOME_CONTEXT);
	}

	public function test_ForceAnsiColor_buildFromArray_ShouldSetForceAnsiColor() {
		$this->writer
			->expects(self::once())
			->method('setForceAnsiColorSupport')
			->with(true);

		$this->strategy->buildFromArray([Console::FORCE_ANSI_COLOR => true], self::SOME_CONTEXT);
	}

	public function test_FalseForceAnsiColor_buildFromArray_ShouldNeverSetForceAnsiColor() {
		$this->writer
			->expects(self::never())
			->method('setForceAnsiColorSupport');

		$this->strategy->buildFromArray([Console::FORCE_ANSI_COLOR => false], self::SOME_CONTEXT);
	}

	public function test_ForceNoAnsiColor_buildFromArray_ShouldSetForceAnsiColor() {
		$this->writer
			->expects(self::once())
			->method('setForceNoAnsiColorSupport')
			->with(true);

		$this->strategy->buildFromArray([Console::FORCE_NO_ANSI_COLOR => true], self::SOME_CONTEXT);
	}

	public function test_FalseForceNoAnsiColor_buildFromArray_ShouldNeverSetForceNoAnsiColor() {
		$this->writer
			->expects(self::never())
			->method('setForceNoAnsiColorSupport');

		$this->strategy->buildFromArray([Console::FORCE_NO_ANSI_COLOR => false], self::SOME_CONTEXT);
	}

	public function test_buildFromArray_ShouldReturnWriter() {
		$actualWriter = $this->strategy->buildFromArray([], self::SOME_CONTEXT);

		$this->assertSame($this->writer, $actualWriter);
	}
}