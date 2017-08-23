<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy;
use Kronos\Log\Factory\Writer As WriterFactory;

class Memory extends AbstractWriter {

	/**
	 * @var WriterFactory
	 */
	private $factory;

	public function __construct(WriterFactory $factory) {
		$this->factory = $factory;
	}

	/**
	 * @param array $settings
	 * @return \Kronos\Log\Writer\Memory
	 */
	public function buildFromArray(array $settings) {
		$writer = $this->factory->createMemoryWriter();

		$this->setCommonSettings($writer, $settings);

		return $writer;
	}
}