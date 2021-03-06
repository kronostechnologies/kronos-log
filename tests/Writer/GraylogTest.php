<?php


namespace Kronos\Tests\Log\Writer;


use Gelf\Logger;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;
use Kronos\Log\Factory\Writer;
use Kronos\Log\Writer\Graylog;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

class GraylogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Kronos\Log\Factory\Graylog&MockObject
     */
    private $factory;

    /**
     * @var Logger&MockObject
     */
    private $logger;

    /**
     * @var Publisher&MockObject
     */
    private $publisher;

    /**
     * @var UdpTransport&MockObject
     */
    private $transport;

    /**
     * @var Graylog
     */
    private $writer;

    public function setUp(): void
    {
        $this->factory = $this->getMockBuilder(\Kronos\Log\Factory\Graylog::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->publisher = $this->getMockBuilder(Publisher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transport = $this->getMockBuilder(UdpTransport::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function test_uninitializedWithHostParamsSet_log_CreatesUdpTransportMatchingParams()
    {
        $givenHostname = '127.0.0.1';
        $givenPort = 12201;
        $givenChunkSize = 8196;
        $this->writer = new Graylog($givenHostname, $givenPort, $givenChunkSize, null, false, $this->factory);

        $this->factory->method('createLogger')->willReturn($this->logger);
        $this->factory->method('createPublisher')->willReturn($this->publisher);
        $this->factory->expects(self::once())
            ->method('createUdpTransport')
            ->with($givenHostname, $givenPort, $givenChunkSize)
            ->willReturn($this->transport);

        $this->writer->log(LogLevel::INFO, 'anything');
    }

    public function test_unitializedWithHostParamsSet_log_BuildsLogger()
    {
        $this->writer = new Graylog('127.0.0.1', 12201, 8196, null, false, $this->factory);
        $this->factory->method('createUdpTransport')->willReturn($this->transport);
        $this->factory->method('createPublisher')->willReturn($this->publisher);

        $this->factory->expects(self::once())->method('createLogger')->with($this->publisher)->willReturn($this->logger);

        $this->writer->log(LogLevel::INFO, 'anything');
    }

    public function test_initialized_logTwice_DoesNotInitializeTwice()
    {
        $this->writer = new Graylog('127.0.0.1', 12201, 8196, null, false, $this->factory);
        $this->factory->method('createUdpTransport')->willReturn($this->transport);
        $this->factory->method('createPublisher')->willReturn($this->publisher);

        $this->factory->expects(self::once())->method('createLogger')->willReturn($this->logger);

        $this->writer->log(LogLevel::INFO, 'anything');
        $this->writer->log(LogLevel::INFO, 'anything');
    }

    public function test_logWithValidLevel_RemapsLevelToNumeric()
    {
        $givenLevel = LogLevel::CRITICAL;
        $this->writer = new Graylog('127.0.0.1', 12201, 8196, null, false, $this->factory);
        $this->factory->method('createLogger')->willReturn($this->logger);
        $this->factory->method('createUdpTransport')->willReturn($this->transport);
        $this->factory->method('createPublisher')->willReturn($this->publisher);

        $this->logger->expects(self::once())->method('log')->with(2, self::anything(), self::anything());

        $this->writer->log($givenLevel, 'Something broke!');
    }

    public function test_logWithMessage_PassesMessageToLogger()
    {
        $givenMessage = 'Something is working.';
        $this->writer = new Graylog('127.0.0.1', 12201, 8196, null, false, $this->factory);
        $this->factory->method('createLogger')->willReturn($this->logger);
        $this->factory->method('createUdpTransport')->willReturn($this->transport);
        $this->factory->method('createPublisher')->willReturn($this->publisher);

        $this->logger->expects(self::once())->method('log')->with(self::anything(), $givenMessage, self::anything());

        $this->writer->log(LogLevel::INFO, $givenMessage);
    }

    public function test_applicationUnset_logWithAdditionalContext_PassesContextToLogger()
    {
        $givenContext = ['errorCode' => 23];
        $this->writer = new Graylog('127.0.0.1', 12201, 8196, null, false, $this->factory);
        $this->factory->method('createLogger')->willReturn($this->logger);
        $this->factory->method('createUdpTransport')->willReturn($this->transport);
        $this->factory->method('createPublisher')->willReturn($this->publisher);

        $this->logger->expects(self::once())->method('log')->with(self::anything(), self::anything(), $givenContext);

        $this->writer->log(LogLevel::INFO, 'Something happened.', $givenContext);
    }

    public function test_applicationSet_log_AppendsApplicationToContext()
    {
        $givenApplication = 'TheApp';
        $this->writer = new Graylog('127.0.0.1', 12201, 8196, $givenApplication, false, $this->factory);
        $this->factory->method('createLogger')->willReturn($this->logger);
        $this->factory->method('createUdpTransport')->willReturn($this->transport);
        $this->factory->method('createPublisher')->willReturn($this->publisher);

        $this->logger->expects(self::once())->method('log')->with(self::anything(), self::anything(),
            ['_app' => $givenApplication]);

        $this->writer->log(LogLevel::INFO, 'Something happened.');
    }

    public function test_applicationSetWithCustomContext_log_AppendsApplicationToContext()
    {
        $givenApplication = 'TheApp';
        $this->writer = new Graylog('127.0.0.1', 12201, 8196, $givenApplication, false, $this->factory);
        $this->factory->method('createLogger')->willReturn($this->logger);
        $this->factory->method('createUdpTransport')->willReturn($this->transport);
        $this->factory->method('createPublisher')->willReturn($this->publisher);

        $this->logger->expects(self::once())->method('log')->with(self::anything(), self::anything(),
            ['_app' => $givenApplication, 'customContextValue' => 123]);

        $this->writer->log(LogLevel::INFO, 'Something happened.', ['customContextValue' => 123]);
    }

    public function test_outputVerboseLevelSet_log_AppendsVerboseLevelToContext()
    {
        $this->writer = new Graylog('127.0.0.1', 12201, 8196, null, true, $this->factory);
        $this->factory->method('createLogger')->willReturn($this->logger);
        $this->factory->method('createUdpTransport')->willReturn($this->transport);
        $this->factory->method('createPublisher')->willReturn($this->publisher);

        $this->logger->expects(self::once())->method('log')->with(self::anything(), self::anything(),
            ['levelVerbose' => LogLevel::INFO]);

        $this->writer->log(LogLevel::INFO, 'Something happened.');
    }

    public function test_outputVerboseLevelSetWithCustomContext_log_AppendsVerboseLevelToContext()
    {
        $this->writer = new Graylog('127.0.0.1', 12201, 8196, null, true, $this->factory);
        $this->factory->method('createLogger')->willReturn($this->logger);
        $this->factory->method('createUdpTransport')->willReturn($this->transport);
        $this->factory->method('createPublisher')->willReturn($this->publisher);

        $this->logger->expects(self::once())->method('log')->with(self::anything(), self::anything(),
            ['customContextValue' => 123, 'levelVerbose' => LogLevel::INFO]);

        $this->writer->log(LogLevel::INFO, 'Something happened.', ['customContextValue' => 123]);
    }

    public function test_exceptionWhenLogging_log_ReturnsFalse()
    {
        $this->writer = new Graylog('127.0.0.1', 12201, 8196, null, false, $this->factory);
        $this->factory->method('createLogger')->willReturn($this->logger);
        $this->factory->method('createUdpTransport')->willReturn($this->transport);
        $this->factory->method('createPublisher')->willReturn($this->publisher);
        $this->logger->method('log')->willThrowException(new \Exception("Connection error"));

        $h = false;
        set_error_handler(function () use (&$h) {
            $h = true;
        }, E_USER_WARNING);
        $retVal = $this->writer->log(LogLevel::INFO, "Anything");
        self::assertTrue($h);

        self::assertFalse($retVal);
    }
}
