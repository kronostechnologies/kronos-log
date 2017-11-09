<?php

namespace Kronos\Log;

class LogLocator {

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private static $logger;

	/**
	 * @param \Psr\Log\LoggerInterface $logger
	 */
	public static function setLogger(\Psr\Log\LoggerInterface $logger) {
		self::$logger = $logger;
	}

	/**
	 * @return \Psr\Log\LoggerInterface
	 */
	public static function getLogger() {
		if(!self::$logger) {
			$settings = [
				[
					'type' => \Kronos\Log\Enumeration\WriterTypes::FILE,
					'settings' => [
						'filename' => '/dev/null'
					]
				]
			];

			$builder = new \Kronos\Log\Builder();
			self::$logger = $builder->buildFromArray($settings);
		}

		return self::$logger;
	}
}