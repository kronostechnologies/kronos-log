<?php

namespace Kronos\Log;

class ContextStringifier {

	private $excluded_keys = [];

	public function stringify(array $context) {
		$string = '';

		$key_count = 0;
		foreach($context as $key => $value) {
			if(!in_array($key, $this->excluded_keys)) {
				$string .= ($key_count++ > 0 ? PHP_EOL : '') . $key . ': ' . $this->stringifyValue($value);
			}
		}

		return $string;
	}

	private function stringifyValue($value) {
		if(is_array($value)) {
			return print_r($value, true);
		}
		if(is_object($value)) {
			return $this->stringifyObject($value);
		}
		else {
			return $value;
		}
	}

	private function stringifyObject($value) {
		if(method_exists($value, '__toString')) {
			return (string)$value;
		}
		else {
			return print_r($value, true);
		}
	}

	public function excludeKey($key) {
		$this->excluded_keys[] = $key;
	}
}