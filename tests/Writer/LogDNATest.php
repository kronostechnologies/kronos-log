<?php

namespace Kronos\Tests\Log\Writer;

use Exception;
use GuzzleHttp\Client;
use Kronos\Log\Formatter\ContextStringifier;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Writer\LogDNA;
use Kronos\Log\Factory;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LogDNATest extends TestCase
{
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
    const CUSTOM_HEADER_VALUE = ['Bar', 'Baz'];
    const CUSTOM_HEADER = 'X-Foo';
    const PROXY = 'tcp://localhost:8125';
    const TIMEOUT = 3.14;
    const STINGIFYIED_CONTEXT = ['field' => 'stringified value'];
    const EXCEPTION_TRACE = 'exception trace';

    private LogDNA $writer;
    private Factory\Guzzle&MockObject $factory;
    private Client&MockObject $client;
    private TraceBuilder&MockObject $exceptionTraceBuilder;
    private TraceBuilder&MockObject $previousExceptionTraceBuilder;
    private ContextStringifier&MockObject $contextStringifier;

    public function setUp(): void
    {
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['post'])
            ->getMock();

        $this->factory = $this->createMock(Factory\Guzzle::class);
        $this->factory->method('createClient')->willReturn($this->client);

        $this->contextStringifier = $this->createMock(ContextStringifier::class);
    }

    public function test_constructor_ShouldCreateGuzzleClient()
    {
        $this->factory
            ->expects(self::once())
            ->method('createClient')
            ->with([
                'headers' => [
                    'Content-Type' => 'application/json',
                    'apikey' => self::INGESTION_KEY,
                    'Connection' => 'keep-alive'
                ],
                'base_uri' => LogDNA::LOGDNA_URL
            ]);

        $this->writer = new LogDNA(
            self::HOSTNAME,
            self::APPLICATION,
            self::INGESTION_KEY,
            [],
            $this->factory,
            null,
            null,
            $this->contextStringifier
        );
    }

    public function test_guzzleOptions_constructor_ShouldCreateGuzzleClientWithMergedOptions()
    {
        $this->factory
            ->expects(self::once())
            ->method('createClient')
            ->with([
                'headers' => [
                    'Content-Type' => 'application/json',
                    'apikey' => self::INGESTION_KEY,
                    'Connection' => 'keep-alive',
                    self::CUSTOM_HEADER => self::CUSTOM_HEADER_VALUE
                ],
                'base_uri' => LogDNA::LOGDNA_URL,
                'proxy' => self::PROXY,
                'timeout' => self::TIMEOUT
            ]);
        $guzzleOptions = [
            'headers' => [
                'Content-Type' => 'not-application/json',
                'apikey' => 'not the ingestion key',
                self::CUSTOM_HEADER => self::CUSTOM_HEADER_VALUE
            ],
            'proxy' => self::PROXY,
            'timeout' => self::TIMEOUT
        ];

        $this->writer = new LogDNA(
            self::HOSTNAME,
            self::APPLICATION,
            self::INGESTION_KEY,
            $guzzleOptions,
            $this->factory,
            null,
            null,
            $this->contextStringifier
        );
    }

    public function test_Context_log_ShouldStringifyContext()
    {
        $this->contextStringifier
            ->expects(self::once())
            ->method('stringifyArray')
            ->with(self::CONTEXT);
        $this->givenWriter();

        $this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE, self::CONTEXT);
    }

    public function test_StringifiedContext_log_ShouldPostMessage()
    {
        $this->client
            ->expects(self::once())
            ->method('post')
            ->with(
                $this->matchesRegularExpression($this->buildUriRegex(LogDNA::INGEST_URI . '?hostname=' . self::HOSTNAME . '&now=\d+')),
                [
                    'json' => [
                        'lines' => [
                            [
                                'line' => self::MESSAGE,
                                'app' => self::APPLICATION,
                                'level' => self::ANY_LOG_LEVEL,
                                'meta' => [LogDNA::METADATA_CONTEXT => self::STINGIFYIED_CONTEXT]
                            ]
                        ]
                    ]
                ]
            );
        $this->givenWriter();
        $this->givenStringifiedContext();

        $this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE, self::CONTEXT);
    }

    public function test_MessageWithInterpolation_log_ShouldPostInterpolatedMessage()
    {
        $this->client
            ->expects(self::once())
            ->method('post')
            ->with(
                $this->matchesRegularExpression($this->buildUriRegex(LogDNA::INGEST_URI . '?hostname=' . urlencode(self::HOSTNAME) . '&now=\d+')),
                [
                    'json' => [
                        'lines' => [
                            [
                                'line' => self::INTERPOLATED_MESSAGE,
                                'app' => self::APPLICATION,
                                'level' => self::ANY_LOG_LEVEL,
                                'meta' => [LogDNA::METADATA_CONTEXT => self::STINGIFYIED_CONTEXT]
                            ]
                        ]
                    ]
                ]
            );
        $this->givenWriter();
        $this->givenStringifiedContext();

        $this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE_WITH_INTERPOLATION, self::CONTEXT);
    }

    public function test_IpAddress_log_ShouldPutIpAddressInUri()
    {
        $this->client
            ->expects(self::once())
            ->method('post')
            ->with(
                $this->matchesRegularExpression($this->buildUriRegex(LogDNA::INGEST_URI . '?hostname=' . urlencode(self::HOSTNAME) . '&now=\d+&ip=' . urlencode(self::IP_ADDRESS))),
                $this->anything()
            );
        $this->givenWriter();
        $this->writer->setIpAddress(self::IP_ADDRESS);

        $this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE);
    }

    public function test_MacAddress_log_ShouldPutMacAddressInUri()
    {
        $this->client
            ->expects(self::once())
            ->method('post')
            ->with(
                $this->matchesRegularExpression($this->buildUriRegex(LogDNA::INGEST_URI . '?hostname=' . urlencode(self::HOSTNAME) . '&now=\d+&mac=' . urlencode(self::MAC_ADDRESS))),
                $this->anything()
            );
        $this->givenWriter();
        $this->writer->setMacAddress(self::MAC_ADDRESS);

        $this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE);
    }

    public function test_ExceptionInContext_log_ShouldReplaceExceptionWithMessage()
    {
        $exception = new TestableException('exception message');
        $this->contextStringifier
            ->expects(self::once())
            ->method('stringifyArray')
            ->with([
                'exception' => [
                    'message' => $exception->getMessage()
                ]
            ]);
        $this->givenWriter();

        $this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE, ['exception' => $exception]);
    }

    public function test_ExceptionInContextAndTraceBuilder_log_ShouldReplaceExceptionWithMessageAndAddStacktrace()
    {
        $this->givenWriterWithExceptionTraceBuilder();
        $exception = new TestableException('exception message');
        $this->exceptionTraceBuilder
            ->expects(self::once())
            ->method('getTraceAsString')
            ->with($exception)
            ->willReturn(self::EXCEPTION_TRACE);
        $this->contextStringifier
            ->expects(self::once())
            ->method('stringifyArray')
            ->with([
                'exception' => [
                    'message' => $exception->getMessage(),
                    'stacktrace' => self::EXCEPTION_TRACE
                ]
            ]);

        $this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE, ['exception' => $exception]);
    }

    public function test_ExceptionWithPreviousExceptionInContext_log_ShouldIncludePreviousExceptionMessage()
    {
        $previousException = new TestableException('previous exception message');
        $exception = new TestableException('exception message', 0, $previousException);
        $this->contextStringifier
            ->expects(self::once())
            ->method('stringifyArray')
            ->with([
                'exception' => [
                    'message' => $exception->getMessage(),
                    'previous' => [
                        'message' => $previousException->getMessage()
                    ]
                ]
            ]);
        $this->givenWriter();

        $this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE, ['exception' => $exception]);
    }

    public function test_ExceptionWithPreviousExceptionInContextAndTraceBuilder_log_ShouldReplaceExceptionWithMessageAndAddStacktrace(
    )
    {
        $this->givenWriterWithPreviousExceptionTraceBuilder();
        $previousException = new TestableException('previous exception message');
        $exception = new TestableException('exception message', 0, $previousException);
        $this->previousExceptionTraceBuilder
            ->expects(self::once())
            ->method('getTraceAsString')
            ->with($previousException)
            ->willReturn(self::EXCEPTION_TRACE);
        $this->contextStringifier
            ->expects(self::once())
            ->method('stringifyArray')
            ->with([
                'exception' => [
                    'message' => $exception->getMessage(),
                    'previous' => [
                        'message' => $previousException->getMessage(),
                        'stacktrace' => self::EXCEPTION_TRACE
                    ]
                ]
            ]);

        $this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE, ['exception' => $exception]);
    }

    public function test_ExceptionStringInContext_log_ShouldKeepExceptionText()
    {
        $this->givenWriter();
        $this->contextStringifier
            ->expects(self::once())
            ->method('stringifyArray')
            ->with([
                'exception' => 'message'
            ]);

        $this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE, ['exception' => 'message']);
    }

    #[DoesNotPerformAssertions]
    public function test_GuzzleClientThrowException_log_ShouldDoNothing()
    {
        $this->client
            ->method('post')
            ->willThrowException(new Exception());
        $this->givenWriter();

        @$this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE);
    }

    private function givenWriter()
    {
        $this->writer = new LogDNA(self::HOSTNAME, self::APPLICATION, self::INGESTION_KEY, [], $this->factory,
            null, null, $this->contextStringifier);
    }

    private function givenWriterWithExceptionTraceBuilder()
    {
        $this->exceptionTraceBuilder = $this->createMock(TraceBuilder::class);

        $this->writer = new LogDNA(self::HOSTNAME, self::APPLICATION, self::INGESTION_KEY, [], $this->factory,
            $this->exceptionTraceBuilder, null, $this->contextStringifier);
    }

    private function givenWriterWithPreviousExceptionTraceBuilder()
    {
        $this->previousExceptionTraceBuilder = $this->createMock(TraceBuilder::class);

        $this->writer = new LogDNA(self::HOSTNAME, self::APPLICATION, self::INGESTION_KEY, [], $this->factory,
            null, $this->previousExceptionTraceBuilder, $this->contextStringifier);
    }

    private function buildUriRegex($uri): string
    {
        return '/' . str_replace(['?', '/', '.'], ['\?', '\/', '\.'], $uri) . '/';
    }

    protected function givenStringifiedContext()
    {
        $this->contextStringifier
            ->method('stringifyArray')
            ->willReturn(self::STINGIFYIED_CONTEXT);
    }
}

class TestableException extends Exception
{
}
