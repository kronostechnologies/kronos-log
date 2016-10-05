<?php

namespace Kronos\Log\Writer;

use Kronos\Log\Adaptor\FileFactory;
use Kronos\Log\ContextStringifier;
use Kronos\Log\Logger;
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

	public function log($level, $message, array $context = []) {
		$this->writeMessage($level, $message, $context);
		$this->writeExceptionIfGiven($context);
		$this->writeContextIfStringifierGiven($context);
	}

	private function writeMessage($level, $message, $context) {
		$interpolated_message = $this->interpolate($message, $context);
		$message_with_loglevel = $this->prependLogLevel($level, $interpolated_message);
		$message_with_datetime = $this->prependDateTime($message_with_loglevel);
		$this->file_adaptor->write($message_with_datetime);
	}

	private function writeContextIfStringifierGiven($context) {
		if($this->context_stringifier) {
			$this->file_adaptor->write(self::CONTEXT_TITLE_LINE);
			$this->file_adaptor->write($this->context_stringifier->stringify($context));
		}
	}

	private function writeExceptionIfGiven($context) {
		if(isset($context[Logger::EXCEPTION_CONTEXT]) && $context[Logger::EXCEPTION_CONTEXT] instanceof Exception) {
			/** @var Exception $exception */
			$exception = $context[Logger::EXCEPTION_CONTEXT];
			$title = strtr(self::EXCEPTION_TITLE_LINE, [
				'{message}' => $exception->getMessage(),
				'{file}' => $exception->getFile(),
				'{line}' => $exception->getLine()
			]);
			$this->file_adaptor->write($title);
			$this->file_adaptor->write($exception->getTraceAsString());

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
		$this->file_adaptor->write($title);
		$this->file_adaptor->write($exception->getTraceAsString());
		$previous = $exception->getPrevious();
		if($previous instanceof Exception) {
			$this->_writePreviousException($previous);
		}
	}
}