<?php

namespace Kronos\Log;

class WriterFactory {

	/**
	 * @var ContextStringifier
	 */
	private $context_stringifier = null;

	public function createFileWriter($filename) {
		$writer = new Writer\File($filename, new Adaptor\File());
		$writer->prependDatetime();
		$writer->prependLogLevel();
		$writer->setContextStringifier($this->getContextStringifier());

		return $writer;
	}

	public function createSyslogWriter($application) {
		return new Writer\Syslog(new Adaptor\Syslog(), $application);
	}

	public function getContextStringifier() {
		if(!$this->context_stringifier) {
			$this->context_stringifier = new ContextStringifier();
		}

		return $this->context_stringifier;
	}
}