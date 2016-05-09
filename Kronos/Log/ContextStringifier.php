<?php

namespace Kronos\Log;

class ContextStringifier {
	public function stringify(array $context) {
		$string = '';

		$key_count = 0;
		foreach($context as $key => $value) {
			$string .= ($key_count++ > 0 ? PHP_EOL : '') . $key . ': ' . $this->stringifyValue($value);
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
}