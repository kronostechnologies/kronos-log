<?php

namespace Kronos\Log;

use Kronos\Log\Adaptor\FileFactory;
use Kronos\Log\Adaptor\Syslog As SyslogAdaptor;

class WriterFactory {

	/**
	 * @var SyslogAdaptor;
	 */
	private $syslog_adaptor;

	/**
	 * @var FileFactory;
	 */
	private $file_factory;

	/**
	 * @var ContextStringifier
	 */
	private $context_stringifier = null;

	/**
	 * @param $filename
	 * @return Writer\File
	 */
	public function createFileWriter($filename) {
		$writer = new Writer\File($filename, $this->getFileFactory());
		$writer->setPrependDatetime();
		$writer->setPrependLogLevel();
		$writer->setContextStringifier($this->getContextStringifier());

		return $writer;
	}

	/**
	 * @param $application
	 * @param int $option
	 * @param int $facility
	 * @return Writer\Syslog
	 */
	public function createSyslogWriter($application, $option= LOG_ODELAY, $facility = LOG_LOCAL0) {
		return new Writer\Syslog($this->getSyslogAdaptor(), $application, $option, $facility);
	}

	/**
	 * @return Writer\Console
	 */
	public function createConsoleWriter() {
		return new Writer\Console($this->getFileFactory());
	}

	/**
	 * @return FileFactory
	 */
	private function getFileFactory() {
		if(!$this->file_factory) {
			$this->file_factory = new FileFactory();
		}

		return $this->file_factory;
	}

	/**
	 * @return SyslogAdaptor
	 */
	private function getSyslogAdaptor() {
		if(!$this->syslog_adaptor) {
			$this->syslog_adaptor = new SyslogAdaptor();
		}

		return $this->syslog_adaptor;
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