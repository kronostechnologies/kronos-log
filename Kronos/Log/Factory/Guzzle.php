<?php

namespace Kronos\Log\Factory;

class Guzzle {
	public function createGuzzleClient(array $options = []) {
		return new \GuzzleHttp\Client($options);
	}
}