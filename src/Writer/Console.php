<?php

namespace Kronos\Log\Writer;

use Kronos\Log\Adaptor\File as FileAdaptor;
use Kronos\Log\Adaptor\FileFactory;
use Kronos\Log\Enumeration\AnsiBackgroundColor;
use Kronos\Log\Enumeration\AnsiTextColor;
use Kronos\Log\Traits\PrependDateTime;
use Kronos\Log\Traits\PrependLogLevel;
use Kronos\Log\Logger;
use Psr\Log\LogLevel;
use \Exception;
use Kronos\Log\Exception\ExceptionTraceBuilder;

class Console extends \Kronos\Log\AbstractWriter {

	use PrependLogLevel;
	use PrependDateTime;

	const STDOUT = 'php://stdout';
	const STDERR = 'php://stderr';
	const EXCEPTION_TITLE_LINE = "Exception: '{message}' in '{file}' at line {line}";
	const PREVIOUS_EXCEPTION_TITLE_LINE = "Previous exception: '{message}' in '{file}' at line {line}";

	/**
	 * @var FileAdaptor
	 */
	private $stdout;

	/**
	 * @var FileAdaptor
	 */
	private $stderr;

	/**
	 * @var ExceptionTraceBuilder
	 */
	private $trace_builder;

	/**
	 * @param FileFactory $factory
	 */
	public function __construct(FileFactory $factory, ExceptionTraceBuilder $trace_builder = null) {
		$this->stdout = $factory->createTTYAdaptor(self::STDOUT);
		$this->stderr = $factory->createTTYAdaptor(self::STDERR);
		$this->trace_builder = is_null($trace_builder) ? new ExceptionTraceBuilder() : $trace_builder;
	}

	/**
	 * @param string $level
	 * @param string $message
	 * @param array $context
	 * @param ExceptionTraceBuilder|null $trace_builder
	 */
	public function log($level, $message, array $context = []) {
		$interpolated_message = $this->interpolate($message, $context);
		$message_with_loglevel = $this->prependLogLevel($level, $interpolated_message);
		$message_with_datetime = $this->prependDateTime($message_with_loglevel);

		if($this->isLevelLower(LogLevel::ERROR, $level)) {
			$this->stdout->write($message_with_datetime, $this->getLevelTextColor($level));
		}
		else {
			$this->stderr->write($message_with_datetime, AnsiTextColor::WHITE, AnsiBackgroundColor::RED);
		}

		$this->writeExceptionIfGiven($message, $level, $context);
	}

	/**
	 * @param bool $force
	 */
	public function setForceAnsiColorSupport($force = true) {
		$this->stdout->setForceAnsiColorSupport($force);
		$this->stderr->setForceAnsiColorSupport($force);
	}

	/**
	 * @param bool $force
	 */
	public function setForceNoAnsiColorSupport($force = true) {
		$this->stdout->setForceNoAnsiColorSupport($force);
		$this->stderr->setForceNoAnsiColorSupport($force);
	}

	/**
	 * @param $level
	 * @return null|string
	 */
	private function getLevelTextColor($level) {
		return ($level == LogLevel::WARNING ? AnsiTextColor::YELLOW : NULL);
	}

	/**
	 * @param string $level
	 * @param array $context
	 */
	private function writeExceptionIfGiven($message, $level, array $context) {
		if(isset($context[Logger::EXCEPTION_CONTEXT]) && $context[Logger::EXCEPTION_CONTEXT] instanceof Exception) {
			/** @var Exception $exception */
			$exception = $context[Logger::EXCEPTION_CONTEXT];
			$this->writeException($message, $level, $exception);
		}
	}

	/**
	 * @param string $level
	 * @param Exception $exception
	 * @param int $depth
	 */
	private function writeException($message, $level, Exception $exception, $depth=0){
		if ($message != $exception->getMessage()){
			$this->writeExceptionTitle($exception, $depth);
		}

		if(! $this->isLevelLower(LogLevel::ERROR, $level)) {
			$ex_trace = $this->trace_builder->getTraceAsString($exception, $this->include_exception_args);
			$this->stderr->write($ex_trace);
		}

		$previous = $exception->getPrevious();
		if($previous instanceof Exception) {
			$this->writeException($message, $level, $previous, $depth+1);
		}
	}

	/**
	 * @param Exception $exception
	 * @param $depth
	 */
	private function writeExceptionTitle(Exception $exception, $depth){
		$title = ($depth === 0 ? self::EXCEPTION_TITLE_LINE : self::PREVIOUS_EXCEPTION_TITLE_LINE);

		$title = strtr($title, [
			'{message}' => $exception->getMessage(),
			'{file}' => $exception->getFile(),
			'{line}' => $exception->getLine()
		]);

		$this->stderr->write($title);
	}
}