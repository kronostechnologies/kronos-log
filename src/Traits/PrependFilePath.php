<?php

namespace Kronos\Log\Traits;

trait PrependFilePath {

	/**
	 * @var bool
	 */
	private $prepend_filepath = false;

	/**
	 * @param bool $prepend_filepath
	 */
	public function setPrependFilePath($prepend_filepath = true) {
		$this->prepend_filepath = $prepend_filepath;
	}

	/**
	 * @param $message
	 * @return string
	 */
	public function prependFilePath($message) {
		if($this->prepend_filepath) {

			$trace = debug_backtrace();
			$file_path = $trace[0]['file'];
			$prefix = '['.$file_path.':'.$trace[0]['line'].'] ';

			return $prefix.$message;
		}
		else {
			return $message;
		}
	}
}