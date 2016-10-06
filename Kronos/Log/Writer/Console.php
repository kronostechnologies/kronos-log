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

	public function __construct(FileFactory $factory) {
		$this->stdout = $factory->createTTYAdaptor(self::STDOUT);
		$this->stderr = $factory->createTTYAdaptor(self::STDERR);
	}

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

		$this->writeExceptionIfGiven($context);
	}

	public function setForceAnsiColorSupport($force = true) {
		$this->stdout->setForceAnsiColorSupport($force);
		$this->stderr->setForceAnsiColorSupport($force);
	}

	public function setForceNoAnsiColorSupport($force = true) {
		$this->stdout->setForceNoAnsiColorSupport($force);
		$this->stderr->setForceNoAnsiColorSupport($force);
	}

	private function getLevelTextColor($level) {
		return ($level == LogLevel::WARNING ? AnsiTextColor::YELLOW : NULL);
	}

	private function writeExceptionIfGiven(array $context) {
		if(isset($context[Logger::EXCEPTION_CONTEXT]) && $context[Logger::EXCEPTION_CONTEXT] instanceof Exception) {
			/** @var Exception $exception */
			$exception = $context[Logger::EXCEPTION_CONTEXT];

			$title = strtr(self::EXCEPTION_TITLE_LINE, [
				'{message}' => $exception->getMessage(),
				'{file}' => $exception->getFile(),
				'{line}' => $exception->getLine()
			]);
			$this->stderr->write($title);
			$this->stderr->write($exception->getTraceAsString());

			$previous = $exception->getPrevious();
			if($previous instanceof Exception) {
				$this->_writePreviousException($previous);
			}
		}
	}

	private function _writePreviousException(Exception $exception){
		$title = strtr(self::PREVIOUS_EXCEPTION_TITLE_LINE, [
			'{message}' => $exception->getMessage(),
			'{file}' => $exception->getFile(),
			'{line}' => $exception->getLine()
		]);
		$this->stderr->write($title);
		$this->stderr->write($exception->getTraceAsString());
		$previous = $exception->getPrevious();
		if($previous instanceof Exception) {
			$this->_writePreviousException($previous);
		}
	}

}