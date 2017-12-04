<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter;
use Kronos\Log\Factory;
use Psr\Log\LogLevel;
use Kronos\Log\Exception\ExceptionTraceBuilder;

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
	 * @var ExceptionTraceBuilder
	 */
	private $trace_builder;

	/**
	 * LogDNA constructor.
	 * @param string $hostname
	 * @param string $application
	 * @param string $ingestionKey
	 * @param array $guzzleOptions
	 * @param GuzzleFactory $guzzleFactory
	 */
	public function __construct($hostname, $application, $ingestionKey, $guzzleOptions = [], Factory\Guzzle $guzzleFactory = null, ExceptionTraceBuilder $trace_builder = null) {
		$this->hostname = $hostname;
		$this->application = $application;
		$this->trace_builder = is_null($trace_builder) ? new ExceptionTraceBuilder() : $trace_builder;

		$this->createGuzzleClient($ingestionKey, $guzzleOptions, $guzzleFactory);
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
	 * @param string $level LogLevel valid string
	 * @param string $message
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

	/**
	 * @return string
	 */
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

	/**
	 * @param mixed $context
	 * @return mixed whatever $context is
	 */
	private function processContext($context) {
		return $this->replaceException($context);
	}

	/**
	 * @param array $context
	 * @return mixed
	 */
	private function replaceException($context) {
		if(isset($context['exception']) && $context['exception'] instanceof \Exception) {
			$exception = $context['exception'];
			unset($context['exception']);

			$context['exception'] = $exception->getMessage();
			$context['stacktrace'] = $this->trace_builder->getTraceAsString($exception, $this->include_exception_args);
		}
		return $context;
	}

	/**
	 * @param string $ingestionKey
	 * @param Factory\Guzzle $guzzleFactory
	 */
	private function createGuzzleClient($ingestionKey, $guzzleOptions, Factory\Guzzle $guzzleFactory = null) {
		$factory = $guzzleFactory ?: new Factory\Guzzle();

		$baseOptions = [
			'headers' => [
				'Content-Type' => 'application/json',
				'apikey' => $ingestionKey
			],
			'base_uri' => self::LOGDNA_URL
		];

		$options = $this->recursiveMerge($baseOptions, $guzzleOptions);

		$this->guzzleClient = $factory->createClient($options);
	}

	private function recursiveMerge($base, $addition) {
		$result = [];

		foreach($base as $index => $value) {
			if(isset($addition[$index])) {
				if(is_array($value) && is_array($addition[$index])) {
					$result[$index] = $this->recursiveMerge($value, $addition[$index]);
				}
				else {
					$result[$index] = $value;
				}
			}
			else {
				$result[$index] = $value;
			}
		}

		foreach($addition as $index => $value) {
			if(!isset($base[$index])) {
				$result[$index] = $value;
			}
		}

		return $result;
	}
}
