<?php

namespace Kronos\Log\Traits;

trait PrependLogLevel {
	private $prepend_log_level = false;

	/**
	 * @param boolean $prepend_log_level
	 */
	public function setPrependLogLevel($prepend_log_level = true) {
		$this->prepend_log_level = $prepend_log_level;
	}

	public function prependLogLevel($level, $message) {
		if($this->prepend_log_level) {
			return strtoupper($level).' : '.$message;
		}
		else {
			return $message;
		}
	}
}