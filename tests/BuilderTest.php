<?php

namespace Kronos\Tests\Log;

use Kronos\Log\AbstractWriter;
use Kronos\Log\Builder;
use Kronos\Log\Exception\NoWriter;
use Kronos\Log\Factory\Logger as LoggerFactory;
use Kronos\Log\Factory\Strategy as StrategyFactory;
use Kronos\Log\Logger;

class BuilderTest extends \PHPUnit_Framework_TestCase {
	const ANY_WRITER_TYPE = 'Console';

	const WRITER_SETTINGS = ['field' => 'value', 'otherField' => 'other value'];

	/**
	 * @var Builder
	 */
	private $builder;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $loggerFactory;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $strategyFactory;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $logger;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $strategy;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $writer;

	public function setUp() {
		$this->logger = self::getMock(Logger::class);
		$this->loggerFactory = self::getMock(LoggerFactory::class);
		$this->loggerFactory->method('createLogger')->willReturn($this->logger);

		$this->strategy = self::getMock(Builder\Strategy::class);
		$this->strategyFactory = self::getMockWithoutInvokingTheOriginalConstructor(StrategyFactory::class);
		$this->strategyFactory->method('createStrategyForType')->willReturn($this->strategy);

		$this->writer = $this->getMock(AbstractWriter::class);
		$this->strategy->method('buildFromArray')->willReturn($this->writer);

		$this->builder = new Builder($this->loggerFactory, $this->strategyFactory);
	}

	public function test_buildFromArray_ShouldCreateLogger() {
		$this->loggerFactory
			->expects(self::once())
			->method('createLogger');

		$this->builder->buildFromArray([['type' => self::ANY_WRITER_TYPE, 'settings' => []]]);
	}

	public function test_SettingsForWriter_buildFromArray_ShouldCreateStrategy() {
		$this->strategyFactory
			->expects(self::once())
			->method('createStrategyForType')
			->with(self::ANY_WRITER_TYPE);

		$this->builder->buildFromArray([['type' => self::ANY_WRITER_TYPE, 'settings' => []]]);
	}

	public function test_Strategy_buildFromArray_ShouldBuildWriterFromArray() {
		$this->strategy
			->expects(self::once())
			->method('buildFromArray')
			->with(self::WRITER_SETTINGS);

		$this->builder->buildFromArray([['type'=> self::ANY_WRITER_TYPE, 'settings' => self::WRITER_SETTINGS]]);
	}

	public function test_Writer_buildFromArray_ShouldAddWriter() {
		$this->logger
			->expects(self::once())
			->method('addWriter')
			->with($this->writer);

		$this->builder->buildFromArray([['type'=> self::ANY_WRITER_TYPE, 'settings' => self::WRITER_SETTINGS]]);
	}

	public function test_AddedWriter_buildFromArray_ShouldReturnLogger() {
		$actualLogger = $this->builder->buildFromArray([['type'=> self::ANY_WRITER_TYPE, 'settings' => self::WRITER_SETTINGS]]);

		$this->assertSame($this->logger, $actualLogger);
	}

	public function test_NoWriterSettings_buildFromArray_ShouldThrowNoWriterException() {
		$this->expectException(NoWriter::class);

		$this->builder->buildFromArray([]);
	}
}