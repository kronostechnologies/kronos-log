<?php

namespace Kronos\Tests\Log\Writer;

use Kronos\Log\Writer\LogDNA;
use Kronos\Log\Factory;
use Psr\Log\LogLevel;

class LogDNATest extends \PHPUnit_Framework_TestCase {
	const INGESTION_KEY = 'ingestionKey';
	const HOSTNAME = 'hostname';
	const APPLICATION = 'application';

	const MESSAGE = 'message';
	const ANY_LOG_LEVEL = LogLevel::INFO;
	const TIMESTAMP = 1497626722;

	const MESSAGE_WITH_INTERPOLATION = 'should replace {field}';
	const INTERPOLATED_MESSAGE = 'should replace value';
	const CONTEXT = ['field' => 'value'];

	const IP_ADDRESS = '10.0.1.101';
	const MAC_ADDRESS = 'C0:FF:EE:C0:FF:EE';
	const SOME_TEXT = 'some text';

	/**
	 * @var LogDNA
	 */
	private $writer;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $factory;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $client;

	public function setUp() {
		$this->client = $this->getMockBuilder(\GuzzleHttp\Client::class)
			->disableOriginalConstructor()
			->setMethods(['post'])
			->getMock();

		$this->factory = $this->getMock(Factory\Guzzle::class);
		$this->factory->method('createClient')->willReturn($this->client);
	}

	public function test_constructor_ShouldCreateGuzzleClient() {
		$this->factory
			->expects(self::once())
			->method('createClient')
			->with([
				'headers' => [
					'Content-Type' => 'application/json',
					'apikey' => self::INGESTION_KEY
				],
				'base_uri' => LogDNA::LOGDNA_URL
			]);

		$this->writer = new LogDNA(self::HOSTNAME, self::APPLICATION, self::INGESTION_KEY, $this->factory);
	}

	public function test_Writer_log_ShouldPostMessage() {
		$this->client
			->expects(self::once())
			->method('post')
			->with(
				$this->matchesRegularExpression($this->buildUriRegex(LogDNA::INGEST_URI.'?hostname='.self::HOSTNAME.'&now=\d+')),
				['json' => [
					'lines' => [
						[
							'line' => self::MESSAGE,
							'app' => self::APPLICATION,
							'level' => self::ANY_LOG_LEVEL,
							'meta' => self::CONTEXT
						]
					]
				]]
			);
		$this->givenWriter();

		$this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE, self::CONTEXT);
	}

	public function test_MessageWithInterpolation_log_ShouldPostInterpolatedMessage() {
		$this->client
			->expects(self::once())
			->method('post')
			->with(
				$this->matchesRegularExpression($this->buildUriRegex(LogDNA::INGEST_URI.'?hostname='.urlencode(self::HOSTNAME).'&now=\d+')),
				['json' => [
					'lines' => [
						[
							'line' => self::INTERPOLATED_MESSAGE,
							'app' => self::APPLICATION,
							'level' => self::ANY_LOG_LEVEL,
							'meta' => self::CONTEXT
						]
					]
				]]
			);
		$this->givenWriter();

		$this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE_WITH_INTERPOLATION, self::CONTEXT);
	}

	public function test_IpAddress_log_ShouldPutIpAddressInUri() {
		$this->client
			->expects(self::once())
			->method('post')
			->with(
				$this->matchesRegularExpression($this->buildUriRegex(LogDNA::INGEST_URI.'?hostname='.urlencode(self::HOSTNAME).'&now=\d+&ip='.urlencode(self::IP_ADDRESS))),
				$this->anything()
			);
		$this->givenWriter();
		$this->writer->setIpAddress(self::IP_ADDRESS);

		$this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE);
	}

	public function test_MacAddress_log_ShouldPutMacAddressInUri() {
		$this->client
			->expects(self::once())
			->method('post')
			->with(
				$this->matchesRegularExpression($this->buildUriRegex(LogDNA::INGEST_URI.'?hostname='.urlencode(self::HOSTNAME).'&now=\d+&mac='.urlencode(self::MAC_ADDRESS))),
				$this->anything()
			);
		$this->givenWriter();
		$this->writer->setMacAddress(self::MAC_ADDRESS);

		$this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE);
	}

	public function test_ExceptionInContext_log_ShouldReplaceExceptionWithMessageAndAddStacktrace() {
		$exception = new \Exception('exception message');
		$this->client
			->expects(self::once())
			->method('post')
			->with(
				$this->anything(),
				['json' => [
					'lines' => [
						[
							'line' => self::MESSAGE,
							'app' => self::APPLICATION,
							'level' => self::ANY_LOG_LEVEL,
							'meta' => [
								'exception' => $exception->getMessage(),
								'stacktrace' => $exception->getTraceAsString()
							]
						]
					]
				]]
			);
		$this->givenWriter();

		$this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE, ['exception' => $exception]);
	}

	public function test_ExceptionStringInContext_log_ShouldKeepExceptionText() {
		$this->client
			->expects(self::once())
			->method('post')
			->with(
				$this->anything(),
				['json' => [
					'lines' => [
						[
							'line' => self::MESSAGE,
							'app' => self::APPLICATION,
							'level' => self::ANY_LOG_LEVEL,
							'meta' => [
								'exception' => self::SOME_TEXT,
							]
						]
					]
				]]
			);
		$this->givenWriter();

		$this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE, ['exception' => self::SOME_TEXT]);
	}

	public function test_GuzzleClientThrowException_log_ShouldDoNothing() {
		$this->client
			->method('post')
			->willThrowException(new \Exception());
		$this->givenWriter();

		$this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE);

	}

	private function givenWriter() {
		$this->writer = new LogDNA(self::HOSTNAME, self::APPLICATION, self::INGESTION_KEY, $this->factory);
	}

	private function buildUriRegex($uri) {
		return '/'.str_replace(['?', '/', '.'], ['\?', '\/', '\.'], $uri).'/';
	}
}