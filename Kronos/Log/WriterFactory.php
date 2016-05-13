<?php

namespace Kronos\Log;

use Kronos\Log\Adaptor\FileFactory;

class WriterFactory {

	/**
	 * @var ContextStringifier
	 */
	private $context_stringifier = null;


	/**
	 * @param $filename
	 * @return Writer\File
	 */
	public function createFileWriter($filename) {
		$writer = new Writer\File($filename, new FileFactory());
		$writer->prependDatetime();
		$writer->prependLogLevel();
		$writer->setContextStringifier($this->getContextStringifier());

		return $writer;
	}

	/**
	 * @param $application
	 * @return Writer\Syslog
	 */
	public function createSyslogWriter($application) {
		return new Writer\Syslog(new Adaptor\Syslog(), $application);
	}

	/**
	 * @return Writer\Console
	 */
	public function createConsoleWriter() {
		return new Writer\Console(new FileFactory());
	}

	/**
	 * @return ContextStringifier
	 */
	public function getContextStringifier() {
		if(!$this->context_stringifier) {
			$this->context_stringifier = new ContextStringifier();
		}

		return $this->context_stringifier;
	}
}