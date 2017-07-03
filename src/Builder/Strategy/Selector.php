<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Factory\Strategy;

class Selector {

	/**
	 * @var Strategy
	 */
	private $factory;

	/**
	 * Selector constructor.
	 * @param Strategy $factory
	 */
	public function __construct(Strategy $factory = null) {
		$this->factory = $factory ?: new Strategy();
	}

	/**
	 * @param string $type
	 * @return \Kronos\Log\Builder\Strategy
	 */
	public function getStrategyForType($type) {

	}
}