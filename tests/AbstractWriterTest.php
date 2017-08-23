<?php

namespace Kronos\Tests\Log;

use Kronos\Log\Exception\InvalidLogLevel;
use \Psr\Log\LogLevel;

class AbstractWriterTest extends \PHPUnit_Framework_TestCase {

	const ANY_LEVEL = LogLevel::INFO;
	const INVALID_LOG_LEVEL = 'invalid';

	const LOWER_LEVEL = LogLevel::NOTICE;
	const HIGHER_LEVEL = LogLevel::CRITICAL;

	/**
	 * @var TestableWriter;
	 */
	private $writer;

	public function setUp() {
		$this->writer = new TestableWriter();
	}

	public function test_NewWriter_CanLogLevel_ShouldReturnTrue() {
		$canLog = $this->writer->canLogLevel(self::ANY_LEVEL);

		$this->assertTrue($canLog);
	}
	
	public function test_NewWriter_CanLogLevelWithInvalidLevel_ShouldThrowInvalidLogLevelException() {
		$this->expectException(InvalidLogLevel::class);

		$this->writer->canLogLevel(self::INVALID_LOG_LEVEL);
	}

	public function test_WriterWithMinLevel_CanLogLevelWithLowerLevel_ShouldReturnFalse() {
		$this->writer->setMinLevel(self::HIGHER_LEVEL);

		$canLog = $this->writer->canLogLevel(self::LOWER_LEVEL);

		$this->assertFalse($canLog);
	}

	public function test_WriterWithMinLevel_CanLogLevelWithHigerLevel_ShouldReturnTrue() {
		$this->writer->setMinLevel(self::LOWER_LEVEL);

		$canLog = $this->writer->canLogLevel(self::HIGHER_LEVEL);

		$this->assertTrue($canLog);
	}

	public function test_NewWriter_SetMinLevelWithInvalidLevel_ShouldThrowInvalidLogLevelException() {
		$this->expectException(InvalidLogLevel::class);

		$this->writer->setMinLevel(self::INVALID_LOG_LEVEL);
	}

	public function test_WriterWithMaxLevel_CanLogLevelWithHigherLevel_SouldReturnFalse() {
		$this->writer->setMaxLevel(self::LOWER_LEVEL);

		$canLog = $this->writer->canLogLevel(self::HIGHER_LEVEL);

		$this->assertFalse($canLog);
	}

	public function test_WriterWithMaxLevel_CanLogLevelWithLowerLevel_SouldReturnTrue() {
		$this->writer->setMaxLevel(self::HIGHER_LEVEL);

		$canLog = $this->writer->canLogLevel(self::LOWER_LEVEL);

		$this->assertTrue($canLog);
	}

	public function test_NewWriter_SetMaxLevelWithInvalidLevel_ShouldThrowInvalidLogLevelException() {
		$this->expectException(InvalidLogLevel::class);

		$this->writer->setMaxLevel(self::INVALID_LOG_LEVEL);
	}
}

class TestableWriter extends \Kronos\Log\AbstractWriter {
	public function log($level, $message, array $context = []) { }
}