<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy;
use Kronos\Log\Factory\Writer As WriterFactory;

class LogDNA extends AbstractWriter {

	const HOSTNAME = 'hostname';
	const APPLICATION = 'application';
	const INGESTION_KEY = 'ingestionKey';

	const IP_ADDRESS = 'ip';
	const MAC_ADDRESS = 'mac';

	/**
	 * @var WriterFactory
	 */
	private $factory;

	public function __construct(WriterFactory $factory) {
		$this->factory = $factory;
	}

	/**
	 * @param array $settings
	 * @return \Kronos\Log\Writer\LogDNA
	 */
	public function buildFromArray(array $settings) {
		$writer = $this->factory->createLogDNAWriter($settings[self::HOSTNAME], $settings[self::APPLICATION], $settings[self::INGESTION_KEY]);

		$this->setCommonSettings($writer, $settings);

		if(isset($settings[self::IP_ADDRESS])) {
			$writer->setIpAddress($settings[self::IP_ADDRESS]);
		}

		if(isset($settings[self::MAC_ADDRESS])) {
			$writer->setMacAddress($settings[self::MAC_ADDRESS]);
		}

		return $writer;
	}
}