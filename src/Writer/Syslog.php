<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter,
	Psr\Log\LogLevel,
	Kronos\Log\Traits\PrependContext;

class Syslog extends AbstractWriter {

	use PrependContext;

	private $log_level_map = [
		LogLevel::EMERGENCY => LOG_EMERG,
		LogLevel::ALERT => LOG_ALERT,
		LogLevel::CRITICAL => LOG_CRIT,
		LogLevel::ERROR => LOG_ERR,
		LogLevel::WARNING => LOG_WARNING,
		LogLevel::NOTICE => LOG_NOTICE,
		LogLevel::INFO => LOG_INFO,
		LogLevel::DEBUG => LOG_DEBUG
	];

	/**
	 * @var \Kronos\Log\Adaptor\Syslog
	 */
	private $syslog_adaptor;

	private $application;
	private $option;
	private $facility;

	/**
	 * Syslog constructor.
	 * @param \Kronos\Log\Adaptor\Syslog $syslog_adaptor
	 * @param $application
	 * @param $option
	 * @param $facility
	 */
	public function __construct(\Kronos\Log\Adaptor\Syslog $syslog_adaptor, $application, $option = LOG_ODELAY, $facility = LOG_LOCAL0) {
		$this->syslog_adaptor = $syslog_adaptor;
		$this->application = $application;
		$this->option = $option;
		$this->facility = $facility;
	}


	private function getSyslogPriorityForLogLevel($level) {
		if(isset($this->log_level_map[$level])) {
			return $this->log_level_map[$level];
		}
		else {
			throw new \Kronos\Log\Exception\InvalidLogLevel($level);
		}
	}

	public function log($level, $message, array $context = []) {
		$interpolated_message = $this->interpolate($message, $context);
		$prepended_message = $this->prependContext($interpolated_message, $context);

		$this->syslog_adaptor->log(
			$this->application,
			$this->option,
			$this->facility,
			$this->getSyslogPriorityForLogLevel($level),
			$prepended_message
		);
	}

}