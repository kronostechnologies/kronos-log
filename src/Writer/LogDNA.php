<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter;
use Kronos\Log\ContextStringifier;
use Kronos\Log\Factory;
use Psr\Log\LogLevel;
use Kronos\Log\Exception\ExceptionTraceBuilder;

class LogDNA extends AbstractWriter {

	const LOGDNA_URL = 'https://logs.logdna.com/';
	const INGEST_URI = 'logs/ingest';

	const METADATA_CONTEXT = 'context';
	const METADATA_USER = 'user';
	const METADATA_EXCEPTION = 'exception';

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
	 * @var ContextStringifier
	 */
	private $contextStringifier;

	/**
	 * LogDNA constructor.
	 * @param $hostname
	 * @param $application
	 * @param $ingestionKey
	 * @param array $guzzleOptions
	 * @param Factory\Guzzle|null $guzzleFactory
	 * @param ExceptionTraceBuilder|null $trace_builder
	 * @param ContextStringifier|null $contextStringifier
	 */
	public function __construct($hostname, $application, $ingestionKey, $guzzleOptions = [], Factory\Guzzle $guzzleFactory = null, ExceptionTraceBuilder $trace_builder = null, ContextStringifier $contextStringifier = null) {
		$this->hostname = $hostname;
		$this->application = $application;
		$this->trace_builder = is_null($trace_builder) ? new ExceptionTraceBuilder() : $trace_builder;

		$this->contextStringifier = $contextStringifier ?: new ContextStringifier();

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
			$metadata = $this->processMetadata($context);

			$this->guzzleClient->post($this->buildUri(), ['json' =>
				['lines' => [
					[
						'line' => $this->interpolate($message, $context),
						'app' => $this->application,
						'level' => $level,
						'meta' => [
							self::METADATA_CONTEXT => (isset($metadata[self::METADATA_CONTEXT])) ? $metadata[self::METADATA_CONTEXT] : []
						]
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
	 * @param $context
	 * @return array whatever $metadata is
	 */
	private function processMetadata(array $context = []) {
		$exception_context = $this->replaceException($context);

		$metadata = [
			self::METADATA_CONTEXT => $this->contextStringifier->stringifyArray($exception_context)
		];

		return $metadata;
	}

	/**
	 * @param array $context
	 * @return mixed
	 */
	private function replaceException($context) {
		if(isset($context['exception']) && $context['exception'] instanceof \Exception) {
			$exception = $context['exception'];
			$context['exception'] = [];

			$context['exception']['message'] = $exception->getMessage();
			$context['exception']['stacktrace'] = $this->trace_builder->getTraceAsString($exception, $this->include_exception_args);
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
				'apikey' => $ingestionKey,
				'Connection' => 'keep-alive'
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
