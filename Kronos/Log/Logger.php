<?php

namespace Kronos\Log;

class Logger extends \Psr\Log\AbstractLogger {

	const EXCEPTION_CONTEXT = 'exception';

	private $context = [];

	/**
	 * @var WriterInterface[]
	 */
	private $writers = [];

	/**
	 * @param WriterInterface $writer
	 */
	public function addWriter(WriterInterface $writer) {
		$this->writers[] = $writer;
	}

	/**
	 * @param string $class
	 * @return WriterInterface
	 * @throws \Exception
	 */
	public function getWriterByClass($class) {

		foreach($this->writers as $writer) {
			if ($writer instanceof $class) {
				return $writer;
			}
		}

		throw new \Exception("Writer does not exist for class {$class}.");
	}

	/**
	 * @param $key String
	 * @param $value Mixed
	 */
	public function addContext($key, $value) {
		$this->context[$key] = $value;
	}

	public function addContextArray(array $context) {
		$this->context = array_merge($this->context, $context);
	}

	public function log($level, $message, array $context = array()) {
		foreach($this->writers as $writer) {
			if($writer->canLogLevel($level)) {
				$writer->log($level, $message, $context + $this->context);
			}
		}
	}
}