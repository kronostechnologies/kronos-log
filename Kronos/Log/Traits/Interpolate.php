<?php

namespace Kronos\Log\Traits;

trait Interpolate {
	public function interpolate($message, array $context = []) {
		$translation = [];
		$placeholders = $this->getPlaceholders($message);
		foreach($placeholders as $placeholder => $key) {
			if(isset($context[$key])) {
				$translation[$placeholder] = (string)$context[$key];
			}
			else {
				$translation[$placeholder] = '~UNDEFINED~';
			}
		}
		return strtr($message, $translation);
	}

	private function getPlaceholders($message) {
		$keys = [];
		preg_match_all('/(\{([a-zA-Z0-9._]+)\})/', $message, $matches, PREG_SET_ORDER);
		foreach($matches as $match) {
			$keys[$match[1]] = $match[2];
		}
		return $keys;
	}

}