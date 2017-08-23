<?php

namespace Kronos\Tests\Log\Writer;

use \Psr\Log\LogLevel;

class SyslogTest extends \PHPUnit_Framework_TestCase {

	const APPLICATION = 'application';
	const SYSLOG_OPTION = LOG_ODELAY;
	const SYSLOG_FACILITY = LOG_LOCAL0;

	const ANY_LOG_LEVEL = LogLevel::NOTICE;
	const A_MESSAGE = 'a message {key}';
	const CONTEXT_KEY = 'key';
	const CONTEXT_VALUE = 'value';
	const INTERPOLATED_MESSAGE = 'a message value';
	const INVALID_LOG_LEVEL = 'invalid log level';

	/**
	 * @var \Kronos\Log\Writer\Syslog
	 */
	private $writer;

	private $syslog_adaptor;
	
	public function setUp() {
		$this->syslog_adaptor = $this->getMock(\Kronos\Log\Adaptor\Syslog::class);

		$this->writer = new \Kronos\Log\Writer\Syslog($this->syslog_adaptor, self::APPLICATION, self::SYSLOG_OPTION, self::SYSLOG_FACILITY);
	}

	public function test_Writer_Log_ShouldCallAdaptorLogWithApplicationOptionAndFacility() {
		$this->expectsAdaptorLogToBeCalledWith(
			self::APPLICATION,
			self::SYSLOG_OPTION,
			self::SYSLOG_FACILITY,
			$this->anything(),
			$this->anything()
		);

		$this->writer->log(self::ANY_LOG_LEVEL, self::A_MESSAGE);
	}

	public function test_Writer_Log_ShouldInterpolateContextAndMessageSendToAdaptor() {
		$this->expectsAdaptorLogToBeCalledWith(
			$this->anything(),
			$this->anything(),
			$this->anything(),
			$this->anything(),
			self::INTERPOLATED_MESSAGE
		);

		$this->writer->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
	}

	public function test_Writer_LogEMERGENCY_ShouldTranslateTo_LOG_EMERG() {
		$this->expectsAdaptorLogToBeCalledWithPriority(LOG_EMERG);

		$this->writer->log(LogLevel::EMERGENCY, self::A_MESSAGE);
	}

	public function test_Writer_LogALERT_ShouldTranslateTo_LOG_ALERT() {
		$this->expectsAdaptorLogToBeCalledWithPriority(LOG_ALERT);

		$this->writer->log(LogLevel::ALERT, self::A_MESSAGE);
	}

	public function test_Writer_LogCRITICAL_ShouldTranslateTo_LOG_CRIT() {
		$this->expectsAdaptorLogToBeCalledWithPriority(LOG_CRIT);

		$this->writer->log(LogLevel::CRITICAL, self::A_MESSAGE);
	}

	public function test_Writer_LogERROR_ShouldTranslateTo_LOG_ERR() {
		$this->expectsAdaptorLogToBeCalledWithPriority(LOG_ERR);

		$this->writer->log(LogLevel::ERROR, self::A_MESSAGE);
	}

	public function test_Writer_LogWARNING_ShouldTranslateTo_LOG_WARNING() {
		$this->expectsAdaptorLogToBeCalledWithPriority(LOG_WARNING);

		$this->writer->log(LogLevel::WARNING, self::A_MESSAGE);
	}

	public function test_Writer_LogNOTICE_ShouldTranslateTo_LOG_NOTICE() {
		$this->expectsAdaptorLogToBeCalledWithPriority(LOG_NOTICE);

		$this->writer->log(LogLevel::NOTICE, self::A_MESSAGE);
	}

	public function test_Writer_LogINFO_ShouldTranslateTo_LOG_INFO() {
		$this->expectsAdaptorLogToBeCalledWithPriority(LOG_INFO);

		$this->writer->log(LogLevel::INFO, self::A_MESSAGE);
	}

	public function test_Writer_LogDEBUG_ShouldTranslateTo_LOG_DEBUG() {
		$this->expectsAdaptorLogToBeCalledWithPriority(LOG_DEBUG);

		$this->writer->log(LogLevel::DEBUG, self::A_MESSAGE);
	}

	public function test_Writer_LogInvalidLevel_ShouldThrowAnInvalidLogLevelException() {
		$this->expectException(\Kronos\Log\Exception\InvalidLogLevel::class);

		$this->writer->log(self::INVALID_LOG_LEVEL, self::A_MESSAGE);
	}

	private function expectsAdaptorLogToBeCalledWith($ident, $option, $facility, $priority, $message) {
		$this->syslog_adaptor
			->expects($this->once())
			->method('log')
			->with(
				$ident,
				$option,
				$facility,
				$priority,
				$message
			);
	}

	private function expectsAdaptorLogToBeCalledWithPriority($priority) {
		$this->expectsAdaptorLogToBeCalledWith(
			$this->anything(),
			$this->anything(),
			$this->anything(),
			$priority,
			$this->anything()
		);
	}
}
