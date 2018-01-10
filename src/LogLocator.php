<?php

namespace Kronos\Log;

class LogLocator {

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private static $logger;

	/**
	 * @param \Psr\Log\LoggerInterface $logger
	 * @param bool $force
	 */
	public static function setLogger(\Psr\Log\LoggerInterface $logger, $force = false) {
		if(!self::isLoggerSet() || $force) {
			self::$logger = $logger;
		}
	}

	/**
	 * @return bool
	 */
	public static function isLoggerSet() {
		return isset(self::$logger);
	}

	/**
	 * @return \Psr\Log\LoggerInterface
	 */
	public static function getLogger() {
		if(!self::isLoggerSet()) {
			$settings = [
				[
					'type' => \Kronos\Log\Enumeration\WriterTypes::FILE,
					'settings' => [
						'filename' => '/dev/null'
					]
				]
			];

			$builder = new \Kronos\Log\Builder();
			self::setLogger($builder->buildFromArray($settings));
		}

		return self::$logger;
	}
}