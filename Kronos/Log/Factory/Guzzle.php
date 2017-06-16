<?php

namespace Kronos\Log\Factory;

class Guzzle {
	public function createClient(array $options = []) {
		return new \GuzzleHttp\Client($options);
	}
}