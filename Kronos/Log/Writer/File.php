<?php

namespace Kronos\Log\Writer;

use Kronos\Log\Adaptor\FileFactory;
use Kronos\Log\ContextStringifier;
use Kronos\Log\Logger;
use Psr\Log\LogLevel;
use Exception;

class File extends \Kronos\Log\AbstractWriter {

	use \Kronos\Log\Traits\PrependDateTime;
	use \Kronos\Log\Traits\PrependLogLevel;

	const EXCEPTION_TITLE_LINE = "Exception: '{message}' in '{file}' at line {line}";
	const PREVIOUS_EXCEPTION_TITLE_LINE = "Previous exception: '{message}' in '{file}' at line {line}";
	const CONTEXT_TITLE_LINE = 'Context:';

	/**
	 * @var \Kronos\Log\Adaptor\File
	 */
	private $file_adaptor;

	/**
	 * @var ContextStringifier
	 */
	private $context_stringifier = NULL;

	/**
	 * @param string $filename
	 * @param FileFactory $factory
	 */
	public function __construct($filename, FileFactory $factory) {
		$this->file_adaptor = $factory->createFileAdaptor($filename);
	}

	/**
	 * @param ContextStringifier $context_stringifier
	 */
	public function setContextStringifier($context_stringifier) {
		$this->context_stringifier = $context_stringifier;
		$this->context_stringifier->excludeKey(Logger::EXCEPTION_CONTEXT);
	}

	/**
	 * @param string $level
	 * @param string $message
	 * @param array $context
	 */
	public function log($level, $message, array $context = []) {
		$this->writeMessage($level, $message, $context);
		$this->writeExceptionIfGiven($level, $context);
		$this->writeContextIfStringifierGiven($context);
	}

	/**
	 * @param string $level
	 * @param string $message
	 * @param array $context
	 */
	private function writeMessage($level, $message, array $context = []) {
		$interpolated_message = $this->interpolate($message, $context);
		$message_with_loglevel = $this->prependLogLevel($level, $interpolated_message);
		$message_with_datetime = $this->prependDateTime($message_with_loglevel);
		$this->file_adaptor->write($message_with_datetime);
	}

	/**
	 * @param array $context
	 */
	private function writeContextIfStringifierGiven(array $context = []) {
		if($this->context_stringifier) {
			$this->file_adaptor->write(self::CONTEXT_TITLE_LINE);
			$this->file_adaptor->write($this->context_stringifier->stringify($context));
		}
	}

	/**
	 * @param string $level
	 * @param array $context
	 */
	private function writeExceptionIfGiven($level, array $context) {
		if(isset($context[Logger::EXCEPTION_CONTEXT]) && $context[Logger::EXCEPTION_CONTEXT] instanceof Exception) {
			/** @var Exception $exception */
			$exception = $context[Logger::EXCEPTION_CONTEXT];
			$this->writeException($level, $exception);
		}
	}

	/**
	 * @param string $level
	 * @param Exception $exception
	 * @param int $depth
	 */
	private function writeException($level, Exception $exception, $depth=0){
		$title = ($depth === 0 ? self::EXCEPTION_TITLE_LINE : self::PREVIOUS_EXCEPTION_TITLE_LINE);
		$title = strtr($title, [
			'{message}' => $exception->getMessage(),
			'{file}' => $exception->getFile(),
			'{line}' => $exception->getLine()
		]);
		$this->file_adaptor->write($title);

		if(! $this->isLevelLower(LogLevel::ERROR, $level)) {
			$this->file_adaptor->write($exception->getTraceAsString());
		}

		$previous = $exception->getPrevious();
		if($previous instanceof Exception) {
			$this->writeException($level, $previous, $depth+1);
		}
	}
}