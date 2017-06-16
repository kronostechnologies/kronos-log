<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter;
use Kronos\Log\Factory;
use Psr\Log\LogLevel;

class LogDNA extends AbstractWriter {

	const LOGDNA_URL = 'https://logs.logdna.com/';
	const INGEST_URI = 'logs/ingest';

	/**
	 * @var string
	 */
	private $hostname;

	/**
	 * @var string
	 */
	private $application;

	/**
	 * @var string
	 */
	private $ip;

	/**
	 * @var string
	 */
	private $mac;

	/**
	 * @var \GuzzleHttp\Client
	 */
	private $guzzleClient;

	/**
	 * LogDNA constructor.
	 * @param string $hostname
	 * @param string $ingestionKey
	 * @param GuzzleFactory $guzzleFactory
	 */
	public function __construct($hostname, $application, $ingestionKey, Factory\Guzzle $guzzleFactory = null) {
		$this->hostname = $hostname;
		$this->application = $application;

		$this->createGuzzleClient($ingestionKey, $guzzleFactory);
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


	/**
	 * @param $level LogLevel valid string
	 * @param $message
	 * @param array $context
	 */
	public function log($level, $message, array $context = []) {
		try {
			$this->guzzleClient->post($this->buildUri(), ['json' =>
				['lines' => [
					[
						'line' => $this->interpolate($message, $context),
						'app' => $this->application,
						'level' => $level,
						'meta' => $this->processContext($context)
					]
				]
			]]);
		}
		catch(\Exception $exception) {
			// A logger should never be the reason why the app crashed.
		}
	}

	private function buildUri() {
		$uri = self::INGEST_URI.'?hostname='.urlencode($this->hostname).'&now='.time();
		if($this->ip) {
			$uri .= '&ip='.urlencode($this->ip);
		}
		if($this->mac) {
			$uri .= '&mac='.urlencode($this->mac);
		}

		return $uri;
	}

	private function processContext($context) {
		return $this->replaceException($context);
	}

	/**
	 * @param $context
	 * @return mixed
	 */
	private function replaceException($context) {
		if(isset($context['exception'])) {
			$exception = $context['exception'];
			unset($context['exception']);

			$context['exception'] = $exception->getMessage();
			$context['stacktrace'] = $exception->getTraceAsString();
		}
		return $context;
	}

	/**
	 * @param $ingestionKey
	 * @param Factory\Guzzle $guzzleFactory
	 */
	private function createGuzzleClient($ingestionKey, Factory\Guzzle $guzzleFactory = null) {
		$factory = $guzzleFactory ?: new Factory\Guzzle();
		$this->guzzleClient = $factory->createClient([
			'headers' => [
				'Content-Type' => 'application/json',
				'apikey' => $ingestionKey
			],
			'base_uri' => self::LOGDNA_URL
		]);
	}
}