<?php

namespace Kronos\Log\Exception;

class InvalidLogLevel extends \Exception {
	public function __construct($level) {
		parent::__construct('Invalid log level : '.$level);
	}
}