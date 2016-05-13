<?php

namespace Kronos\Log\Writer;

use Kronos\Log\Adaptor\FileFactory;
use Kronos\Log\ContextStringifier;
use Kronos\Log\Logger;

class File extends \Kronos\Log\AbstractWriter {

	use \Kronos\Log\Traits\PrependDateTime;
	use \Kronos\Log\Traits\PrependLogLevel;

	const EXCEPTION_TITLE_LINE = 'Exception:';
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
	 * @param \Kronos\Log\Adaptor\File $file_adaptor
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
		if(isset($context[Logger::EXCEPTION_CONTEXT]) && $context[Logger::EXCEPTION_CONTEXT] instanceof \Exception) {
			$exception = $context[Logger::EXCEPTION_CONTEXT];
			$this->file_adaptor->write(self::EXCEPTION_TITLE_LINE);
			$this->file_adaptor->write($exception->getMessage());
			$this->file_adaptor->write($exception->getTraceAsString());
		}
	}


}