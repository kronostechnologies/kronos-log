<?php

namespace Kronos\Log;

use Kronos\Log\Builder\Strategy\Selector;
use Kronos\Log\Exception\NoWriter;
use Kronos\Log\Factory\Logger as LoggerFactory;
use phpDocumentor\Reflection\Types\Context;
use Kronos\Log\Enumeration\ConfigContext;

class Builder {

	/**
	 * @var LoggerFactory
	 */
	private $loggerFactory;

	/**
	 * @var Selector
	 */
	private $selector;

    /**
     * @var \Kronos\Log\Enumeration\ConfigContext
     */
	private $context;

	/**
	 * Builder constructor.
	 * @param LoggerFactory $loggerFactory
	 * @param Selector $selector
	 */
	public function __construct(LoggerFactory $loggerFactory = null, Selector $selector = null, $context = \Kronos\Log\Enumeration\ConfigContext::APP) {
		$this->loggerFactory = $loggerFactory ?: new LoggerFactory();
		$this->selector = $selector ?: new Selector();
		$this->context = $context;
	}


	/**
	 * @param array $settings
	 * @return Logger
	 * @throws NoWriter
	 */
	public function buildFromArray(array $settings) {
		$logger = $this->loggerFactory->createLogger();

		if(empty($settings)) {
			throw new NoWriter('Logger should have at least one writer');
		}

		foreach($settings as $writerSetting) {
			$strategy = $this->selector->getStrategyForType($writerSetting['type']);
			$writer = $strategy->buildFromArray($writerSetting['settings'], $this->context);
			$logger->addWriter($writer);
		}

		return $logger;
	}
}