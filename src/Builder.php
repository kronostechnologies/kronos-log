<?php

namespace Kronos\Log;

use Kronos\Log\Factory\Logger as LoggerFactory,
	Kronos\Log\Factory\Writer as WriterFactory;

class Builder {

	/**
	 * @var LoggerFactory
	 */
	private $loggerFactory;

	/**
	 * @var WriterFactory
	 */
	private $writerFactory;

	public function buildFromArray(array $settings) {

	}
}