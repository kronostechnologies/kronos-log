<?php

namespace Kronos\Log\Factory;

class Logger {

	/**
	 * @return \Kronos\Log\Logger
	 */
	public function createLogger() {
		return new \Kronos\Log\Logger();
	}
}