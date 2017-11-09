<?php

namespace Kronos\Log;

class LogService {

	/**
	 * System is unusable.
	 *
	 * @param string $message
	 * @param array $context
	 */
	public static function emergency($message, array $context = array()) {
		LogLocator::getLogger()->emergency($message, $context);
	}

	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param string $message
	 * @param array $context
	 */
	public static function alert($message, array $context = array()) {
		LogLocator::getLogger()->alert($message, $context);
	}

	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string $message
	 * @param array $context
	 */
	public static function critical($message, array $context = array()) {
		LogLocator::getLogger()->critical($message, $context);
	}

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 * @param array $context
	 */
	public static function error($message, array $context = array()) {
		LogLocator::getLogger()->error($message, $context);
	}

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string $message
	 * @param array $context
	 */
	public static function warning($message, array $context = array()) {
		LogLocator::getLogger()->warning($message, $context);
	}

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 * @param array $context
	 */
	public static function notice($message, array $context = array()) {
		LogLocator::getLogger()->notice($message, $context);
	}

	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message
	 * @param array $context
	 */
	public static function info($message, array $context = array()) {
		LogLocator::getLogger()->info($message, $context);
	}

	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 * @param array $context
	 */
	public static function debug($message, array $context = array()) {
		LogLocator::getLogger()->debug($message, $context);
	}


	/**
	 * Log Error with exception context
	 * @param $message
	 * @param \Exception $exception
	 * @param array $context
	 */
	public static function exception($message, \Exception $exception, array $context = array()) {
		$context[Logger::EXCEPTION_CONTEXT] = $exception;
		LogLocator::getLogger()->error($message, $context);
	}
}