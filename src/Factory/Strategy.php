<?php

namespace Kronos\Log\Factory;

class Strategy {

	/**
	 * @var Writer
	 */
	private $writerFactory;

	/**
	 * Strategy constructor.
	 * @param Writer $writerFactory
	 */
	public function __construct(Writer $writerFactory) {
		$this->writerFactory = $writerFactory;
	}

	/**
	 * @param string $type
	 * @return \Kronos\Log\Builder\Strategy
	 */
	public function createStrategyForType($type) {

	}
}