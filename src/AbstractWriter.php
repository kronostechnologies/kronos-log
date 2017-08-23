<?php

namespace Kronos\Log;

use Kronos\Log\Exception\InvalidLogLevel;
use Kronos\Log\Traits\Interpolate;
use \Psr\Log\LogLevel;

abstract class AbstractWriter implements WriterInterface {

	use Interpolate;

	/**
	 * Psr\Log\LogLevel priorities, please consider this a const until php 5.6 is officialy used.
	 * @var array
	 */
	protected $level_priorities = [
		LogLevel::EMERGENCY => 7,
		LogLevel::ALERT => 6,
		LogLevel::CRITICAL => 5,
		LogLevel::ERROR => 4,
		LogLevel::WARNING => 3,
		LogLevel::NOTICE => 2,
		LogLevel::INFO => 1,
		LogLevel::DEBUG => 0
	];

	protected $min_level = LogLevel::DEBUG;
	protected $max_level = LogLevel::EMERGENCY;

	public function canLogLevel($level) {
		$this->validateLogLevel($level);

		if($this->isLevelLower($this->min_level, $level) || $this->isLevelHigher($this->max_level, $level)) {
			return false;
		}

		return true;
	}

	protected function validateLogLevel($level) {
		if(!isset($this->level_priorities[$level])) {
			throw new InvalidLogLevel($level);
		}
	}
	
	public function setMinLevel($level) {
		$this->validateLogLevel($level);

		$this->min_level = $level;
	}

	protected function isLevelLower($base_level, $compared_level) {
		return $this->level_priorities[$compared_level] < $this->level_priorities[$base_level];
	}

	public function setMaxLevel($level) {
		$this->validateLogLevel($level);

		$this->max_level = $level;
	}

	protected function isLevelHigher($base_level, $compared_level) {
		return $this->level_priorities[$compared_level] > $this->level_priorities[$base_level];
	}
}