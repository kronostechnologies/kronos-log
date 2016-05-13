<?php

namespace Kronos\Log\Traits;

trait PrependContext {

	private $prepended_keys = [];

	public function addContextKeyToPrepend($key) {
		$this->prepended_keys[] = $key;
	}

	public function prependContext($message, $context) {
		foreach(array_reverse($this->prepended_keys) as $key) {
			if(isset($context[$key])) {
				$message = (string)$context[$key].' '.$message;
			}
		}

		return $message;
	}
}