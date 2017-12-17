<?php

namespace Kronos\Log\Adaptor;

class Syslog {

	private static $facilities = [
		LOG_AUTH,
		LOG_AUTHPRIV,
		LOG_CRON,
		LOG_DAEMON,
		LOG_KERN,
		LOG_LOCAL0,
		LOG_LOCAL1,
		LOG_LOCAL2,
		LOG_LOCAL3,
		LOG_LOCAL4,
		LOG_LOCAL5,
		LOG_LOCAL6,
		LOG_LOCAL7,
		LOG_LPR,
		LOG_MAIL,
		LOG_NEWS,
		LOG_SYSLOG,
		LOG_USER,
		LOG_UUCP
	];

	private static $priorities = [
		LOG_EMERG,
		LOG_ALERT,
		LOG_CRIT,
		LOG_ERR,
		LOG_WARNING,
		LOG_NOTICE,
		LOG_INFO,
		LOG_DEBUG
	];

	private static $current_ident = NULL;
	private static $current_facility = NULL;


	/**
	 * @param string $ident openlog ident parameter
	 * @param int $option openlog option parameter
	 * @param int $facility openlog facility parameter
	 * @param int $priority syslog prioriry parameter
	 * @param string $message syslog message parameter
	 */
	public function log($ident, $option, $facility, $priority, $message) {
		$this->checkFacility($facility);
		$this->checkPriority($priority);

		if(!$this->isCurrentLog($ident, $facility)) {
			$this->switchToLog($ident, $option, $facility);
		}

		$this->write($priority, $message);
	}

	private function isCurrentLog($ident, $facility) {
		return self::$current_ident == $ident && self::$current_facility == $facility;
	}
	
	private function switchToLog($ident, $option, $facility) {
		if(openlog($ident, $option, $facility)) {
			self::$current_ident = $ident;
			self::$current_facility = $facility;
		}
	}
	
	private function write($priority, $message) {
		syslog($priority, $message);
	}

	private function checkFacility($facility) {
		if(!in_array($facility, self::$facilities)) {
			throw new \Exception('Invalid syslog facility');
		}
	}

	private function checkPriority($priority) {
		if(!in_array($priority, self::$priorities)) {
			throw new \Exception('Invalid syslog priority');
		}
	}
}