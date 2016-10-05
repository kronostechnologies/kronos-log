<?php

namespace Kronos\Log\Factory;

use Kronos\Log\Adaptor\FileFactory;
use Kronos\Log\Adaptor\Syslog As SyslogAdaptor;
use Kronos\Log\ContextStringifier;
use Kronos\Log\Writer\File;
use Kronos\Log\Writer\Syslog;
use Kronos\Log\Writer\Console;

class Writer {

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
	 * @return File
	 */
	public function createFileWriter($filename) {
		$writer = new File($filename, $this->getFileFactory());
		$writer->setPrependDateTime();
		$writer->setPrependLogLevel();
		$writer->setContextStringifier($this->getContextStringifier());

		return $writer;
	}

	/**
	 * @param $application
	 * @param int $option
	 * @param int $facility
	 * @return Syslog
	 */
	public function createSyslogWriter($application, $option= LOG_ODELAY, $facility = LOG_LOCAL0) {
		return new Syslog($this->getSyslogAdaptor(), $application, $option, $facility);
	}

	/**
	 * @return Console
	 */
	public function createConsoleWriter() {
		return new Console($this->getFileFactory());
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