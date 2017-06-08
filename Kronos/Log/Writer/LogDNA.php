<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter;
use Kronos\Log\Factory;

class LogDNA extends AbstractWriter {

	const INGEST_URL = 'https://logs.logdna.com/logs/ingest';

	/**
	 * @var string
	 */
	private $hostname;

	/**
	 * @var string
	 */
	private $ingestionKey;

	/**
	 * @var string
	 */
	private $ip;

	/**
	 * @var string
	 */
	private $mac;

	/**
	 * @var Factory\Guzzle
	 */
	private $guzzleFactory;

	/**
	 * LogDNA constructor.
	 * @param string $hostname
	 * @param string $ingestionKey
	 * @param GuzzleFactory $guzzleFactory
	 */
	public function __construct($hostname, $ingestionKey, Factory\Guzzle $guzzleFactory = null) {
		$this->hostname = $hostname;
		$this->ingestionKey = $ingestionKey;
		$this->guzzleFactory = $guzzleFactory ?: new Factory\Guzzle();
	}

	/**
	 * @param string $ip
	 */
	public function setIpAddress($ip) {
		$this->ip = $ip;
	}

	/**
	 * @param string $mac
	 */
	public function setMacAddress($mac) {
		$this->mac = $mac;
	}



	public function log($level, $message, array $context = []) {

	}

}