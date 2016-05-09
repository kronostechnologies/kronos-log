<?php

namespace Kronos\Log\Traits;

trait PrependDateTime {
	private $prepend_datetime = false;

	/**
	 * @param boolean $prepend_datetime
	 */
	public function setPrependDateTime($prepend_datetime = true) {
		$this->prepend_datetime = $prepend_datetime;
	}

	public function prependDateTime($message) {
		if($this->prepend_datetime) {
			return '['.date('Y-m-d H:i:s').'] '.$message;
		}
		else {
			return $message;
		}
	}
}