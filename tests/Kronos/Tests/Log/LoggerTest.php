<?php

namespace Kronos\Tests\Log;

use Kronos\Log\Logger;
use Psr\Log\LogLevel;

class LoggerTest extends \PHPUnit_Framework_TestCase {

	const ANY_LOG_LEVEL = LogLevel::INFO;
	const A_MESSAGE = 'some messge';
	const A_CONTEXT_KEY = 'key';
	const A_CONTEXT_VALUE = 'value';
	const ANOTHER_CONTEXT_KEY = 'another key';
	const ANOTHER_CONTEXT_VALUE = 'another value';


	/**
	 * @var \Kronos\Log\Logger
	 */
	private $logger;

	private $writer;

	public function setUp() {
		$this->writer = $this->getMock(\Kronos\Log\WriterInterface::class);

		$this->logger = new Logger();
		$this->logger->addWriter($this->writer);
	}

	public function test_LoggerWithWriter_Log_ShouldAskWriteCanLogLevel() {
		$this->writer
			->expects($this->once())
			->method('canLogLevel')
			->with(self::ANY_LOG_LEVEL);

		$this->logger->log(self::ANY_LOG_LEVEL, self::A_MESSAGE);
	}

	public function test_LoggerWithWriterThatCanLog_Log_ShouldTellWriterToLog() {
		$this->givenWriterCanLog();
		$this->expectsWriterLogToBeCalledWith(self::ANY_LOG_LEVEL, self::A_MESSAGE, [self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE]);

		$this->logger->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, [self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE]);
	}

	public function test_LoggerWithWritterThatCannotLog_Log_ShouldNotCallLogOnWriter() {
		$this->writer->method('canLogLevel')->willReturn(false);
		$this->writer->expects($this->never())->method('log');

		$this->logger->log(self::ANY_LOG_LEVEL, self::A_MESSAGE);
	}

	public function test_LoggerWithContextAndWriter_Log_ShouldAddGivenContext() {
		$this->givenWriterCanLog();
		$this->logger->addContext(self::ANOTHER_CONTEXT_KEY, self::ANOTHER_CONTEXT_VALUE);
		$this->expectsWriterLogToBeCalledWith(
			self::ANY_LOG_LEVEL,
			self::A_MESSAGE,
			[
				self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE,
				self::ANOTHER_CONTEXT_KEY => self::ANOTHER_CONTEXT_VALUE
			]
		);

		$this->logger->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, [self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE]);
	}

	public function test_LoggerContextArrayAndWriter_Log_ShouldAddGivenContext() {
		$this->givenWriterCanLog();
		$this->logger->addContextArray([self::ANOTHER_CONTEXT_KEY => self::ANOTHER_CONTEXT_VALUE]);
		$this->expectsWriterLogToBeCalledWith(
			self::ANY_LOG_LEVEL,
			self::A_MESSAGE,
			[
				self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE,
				self::ANOTHER_CONTEXT_KEY => self::ANOTHER_CONTEXT_VALUE
			]
		);

		$this->logger->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, [self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE]);
	}

	private function givenWriterCanLog() {
		$this->writer->method('canLogLevel')->willReturn(true);
	}

	private function expectsWriterLogToBeCalledWith($level, $message, $context) {
		$this->writer
			->expects($this->once())
			->method('log')
			->with($level, $message, $context);
	}
}