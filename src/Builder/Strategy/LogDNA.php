<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy;
use Kronos\Log\Exception\RequiredSetting;
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

	public function __construct(WriterFactory $factory = null) {
		$this->factory = is_null($factory) ? new WriterFactory() : $factory;
	}

	/**
	 * @param array $settings
	 * @return \Kronos\Log\Writer\LogDNA
	 * @throws RequiredSetting
	 */
	public function buildFromArray(array $settings) {
		$this->checkRequiredSettings($settings);

		$writer = $this->factory->createLogDNAWriter($this->getHostName($settings), $settings[self::APPLICATION], $settings[self::INGESTION_KEY]);

		$this->setCommonSettings($writer, $settings);

		if(isset($settings[self::IP_ADDRESS])) {
			$writer->setIpAddress($settings[self::IP_ADDRESS]);
		}

		if(isset($settings[self::MAC_ADDRESS])) {
			$writer->setMacAddress($settings[self::MAC_ADDRESS]);
		}

		return $writer;
	}

	/**
	 * @param array $settings
	 * @throws RequiredSetting
	 */
	private function checkRequiredSettings(array $settings) {
		$this->throwIfMissing($settings, self::INGESTION_KEY);
	}

	/**
	 * @param $settings
	 * @param $index
	 * @throws RequiredSetting
	 */
	private function throwIfMissing($settings, $index) {
		if(!isset($settings[$index])) {
			throw new RequiredSetting($index.' setting is required');
		}
	}

	/**
	 * Obtains the hostname from the settings array
	 * if not set, we use the server hostname instead.
	 *
	 * @param $settings
	 * @return string
	 * @throws RequiredSetting
	 */
	private function getHostName($settings){
		if(isset($settings[self::HOSTNAME]) && $settings[self::HOSTNAME]){
			return $settings[self::HOSTNAME];
		}
		else{
			$hostname = gethostname();

			if (!$hostname){
				throw new RequiredSetting('Hostname setting is false or null. Please either specify a hostname in the config file or remove it.');
			}

			return $hostname;
		}
	}
}