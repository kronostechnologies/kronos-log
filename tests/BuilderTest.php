<?php

namespace Kronos\Tests\Log;

use Kronos\Log\Builder;
use Kronos\Log\Factory\Logger as LoggerFactory;
use Kronos\Log\Factory\Strategy as StrategyFactory;

class BuilderTest extends \PHPUnit_Framework_TestCase {


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

	public function setUp() {
		$this->loggerFactory = self::getMock(LoggerFactory::class);
		$this->strategyFactory = self::getMock(StrategyFactory::class);

		$this->builder = new Builder($this->loggerFactory, $this->strategyFactory);
	}

	public function test_buildFromArray_ShouldCreateLogger() {
		$this->loggerFactory
			->expects(self::once())
			->method('createLogger');

		$this->builder->buildFromArray([]);
	}

	public function test_SettingsForConsoleWriter_buildFromArray_ShouldCreateConsoleStrategy() {
//		$this->givenLogger();
//		$this->writerFactory
//			->expects(self::once())
//			->method('createConsoleWriter');


	}
}