<?php

namespace Kronos\Log;

use Kronos\Log\Exception\NoWriter;
use Kronos\Log\Factory\Logger as LoggerFactory,
	Kronos\Log\Factory\Strategy as StrategyFactory;

class Builder {

	/**
	 * @var LoggerFactory
	 */
	private $loggerFactory;

	/**
	 * @var StrategyFactory
	 */
	private $strategyFactory;

	/**
	 * Builder constructor.
	 * @param LoggerFactory $loggerFactory
	 * @param StrategyFactory $strategyFactory
	 */
	public function __construct(LoggerFactory $loggerFactory = null, StrategyFactory $strategyFactory = null) {
		$this->loggerFactory = $loggerFactory ?: new LoggerFactory();
		$this->strategyFactory = $strategyFactory ?: new StrategyFactory();
	}


	public function buildFromArray(array $settings) {
		$logger = $this->loggerFactory->createLogger();

		if(empty($settings)) {
			throw new NoWriter('Logger should have at least one writer');
		}

		foreach($settings as $writerSetting) {
			$strategy = $this->strategyFactory->createStrategyForType($writerSetting['type']);
			$writer = $strategy->buildFromArray($writerSetting['settings']);
			$logger->addWriter($writer);
		}

		return $logger;
	}
}