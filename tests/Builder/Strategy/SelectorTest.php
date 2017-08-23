<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\Console;
use Kronos\Log\Builder\Strategy\File;
use Kronos\Log\Builder\Strategy\LogDNA;
use Kronos\Log\Builder\Strategy\Memory;
use Kronos\Log\Builder\Strategy\Selector;
use Kronos\Log\Builder\Strategy\Syslog;
use Kronos\Log\Enumeration\WriterTypes;
use Kronos\Log\Exception\UnsupportedType;
use Kronos\Log\Factory\Strategy;
use Kronos\Log\Writer\Sentry;

class SelectorTest extends \PHPUnit_Framework_TestCase {
	const UNSUPPORTED_TYPE = 'unsupported';

	/**
	 * @var Selector
	 */
	private $selector;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $factory;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $strategy;

	public function setUp() {
		$this->factory = $this->getMockWithoutInvokingTheOriginalConstructor(Strategy::class);

		$this->selector = new Selector($this->factory);
	}

	public function test_Console_getStrategyForType_ShouldCreateConsoleStrategyAndReturnIt() {
		$this->strategy = $this->getMockWithoutInvokingTheOriginalConstructor(Console::class);
		$this->factory
			->expects(self::once())
			->method('createConsoleStrategy')
			->willReturn($this->strategy);

		$actualStrategy = $this->selector->getStrategyForType(WriterTypes::CONSOLE);

		$this->assertSame($this->strategy, $actualStrategy);
	}

	public function test_File_getStrategyForType_ShouldCreateFileStrategyAndReturnIt() {
		$this->strategy = $this->getMockWithoutInvokingTheOriginalConstructor(File::class);
		$this->factory
			->expects(self::once())
			->method('createFileStrategy')
			->willReturn($this->strategy);

		$actualStrategy = $this->selector->getStrategyForType(WriterTypes::FILE);

		$this->assertSame($this->strategy, $actualStrategy);
	}

	public function test_LogDNA_getStrategyForType_ShouldCreateLogDNAStrategyAndReturnIt() {
		$this->strategy = $this->getMockWithoutInvokingTheOriginalConstructor(LogDNA::class);
		$this->factory
			->expects(self::once())
			->method('createLogDNAStrategy')
			->willReturn($this->strategy);

		$actualStrategy = $this->selector->getStrategyForType(WriterTypes::LOGDNA);

		$this->assertSame($this->strategy, $actualStrategy);
	}

	public function test_Memory_getStrategyForType_ShouldCreateMemoryStrategyAndReturnIt() {
		$this->strategy = $this->getMockWithoutInvokingTheOriginalConstructor(Memory::class);
		$this->factory
			->expects(self::once())
			->method('createMemoryStrategy')
			->willReturn($this->strategy);

		$actualStrategy = $this->selector->getStrategyForType(WriterTypes::MEMORY);

		$this->assertSame($this->strategy, $actualStrategy);
	}

	public function test_Sentry_getStrategyForType_ShouldCreateSentryStrategyAndReturnIt() {
		$this->strategy = $this->getMockWithoutInvokingTheOriginalConstructor(Sentry::class);
		$this->factory
			->expects(self::once())
			->method('createSentryStrategy')
			->willReturn($this->strategy);

		$actualStrategy = $this->selector->getStrategyForType(WriterTypes::SENTRY);

		$this->assertSame($this->strategy, $actualStrategy);
	}

	public function test_Syslog_getStrategyForType_ShouldCreateSyslogStrategyAndReturnIt() {
		$this->strategy = $this->getMockWithoutInvokingTheOriginalConstructor(Syslog::class);
		$this->factory
			->expects(self::once())
			->method('createSyslogStrategy')
			->willReturn($this->strategy);

		$actualStrategy = $this->selector->getStrategyForType(WriterTypes::SYSLOG);

		$this->assertSame($this->strategy, $actualStrategy);
	}

	public function test_UnsuppertedType_getStrategyForType_ShouldThrowUnsupportedTypeException() {
		$this->expectException(UnsupportedType::class);

		$this->selector->getStrategyForType(self::UNSUPPORTED_TYPE);
	}
}