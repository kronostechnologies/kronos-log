<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy;
use Kronos\Log\Factory\Writer As WriterFactory;

class Memory implements Strategy {

	/**
	 * @var WriterFactory
	 */
	private $factory;

	public function __construct(WriterFactory $factory) {
		$this->factory = $factory;
	}

	/**
	 * @param array $settings
	 * @return \Kronos\Log\Writer\Console
	 */
	public function buildFromArray(array $settings) {
		// TODO: Implement buildFromArray() method.
	}
}