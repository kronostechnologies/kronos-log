<?php

namespace Kronos\Log\Writer;

use Kronos\Log\Adaptor\File;
use Kronos\Log\Adaptor\FileFactory;
use Kronos\Log\Enumeration\AnsiBackgroundColor;
use Kronos\Log\Enumeration\AnsiTextColor;
use Kronos\Log\Traits\PrependDateTime;
use Kronos\Log\Traits\PrependLogLevel;
use Psr\Log\LogLevel;

class Console extends \Kronos\Log\AbstractWriter {

	use PrependLogLevel;
	use PrependDateTime;

	const STDOUT = 'php://stdout';
	const STDERR = 'php://stderr';

	/**
	 * @var File
	 */
	private $stdout;

	/**
	 * @var File
	 */
	private $stderr;

	public function __construct(FileFactory $factory) {
		$this->stdout = $factory->createTTYAdaptor(self::STDOUT);
		$this->stderr = $factory->createTTYAdaptor(self::STDERR);
	}

	public function log($level, $message, array $context = []) {
		$interpolated_message = $this->interpolate($message, $context);
		$message_with_loglevel = $this->prependLogLevel($level, $interpolated_message);
		$message_with_datetime = $this->prependDateTime($message_with_loglevel);

		if($this->isLevelLower(LogLevel::ERROR, $level)) {
			$this->stdout->write($message_with_datetime, $this->getLevelTextColor($level));
		}
		else {
			$this->stderr->write($message_with_datetime, AnsiTextColor::WHITE, AnsiBackgroundColor::RED);
		}
	}

	public function setForceAnsiColorSupport($force = true) {
		$this->stdout->setForceAnsiColorSupport($force);
		$this->stderr->setForceAnsiColorSupport($force);
	}

	public function setForceNoAnsiColorSupport($force = true) {
		$this->stdout->setForceNoAnsiColorSupport($force);
		$this->stderr->setForceNoAnsiColorSupport($force);
	}

	private function getLevelTextColor($level) {
		return ($level == LogLevel::WARNING ? AnsiTextColor::YELLOW : NULL);
	}

}