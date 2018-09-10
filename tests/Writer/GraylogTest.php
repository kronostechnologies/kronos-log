<?php


namespace Kronos\Tests\Log\Writer;


use Gelf\Logger;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;
use Kronos\Log\Factory\Writer;
use Kronos\Log\Writer\Graylog;

class GraylogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Kronos\Log\Factory\Graylog|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var Publisher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $publisher;

    /**
     * @var UdpTransport|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transport;

    /**
     * @var Graylog
     */
    private $writer;

    public function setUp()
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
        $this->writer = new Graylog($givenHostname, $givenPort, $givenChunkSize, null, $this->factory);

        $this->factory->expects($this->once())
            ->method('createUdpTransport')
            ->with($givenHostname, $givenPort, $givenChunkSize)
            ->willReturn($this->transport);

        $this->writer->log('INFO', 'anything');
    }

    public function test_unitializedWithHostParamsSet_log_BuildsLogger()
    {
        $this->writer = new Graylog('127.0.0.1', 12201, 8196, null, $this->factory);
        $this->factory->method('createUdpTransport')->willReturn($this->transport);
        $this->factory->method('createPublisher')->willReturn($this->publisher);

        $this->factory->expects($this->once())->method('createLogger')->with($this->publisher);

        $this->writer->log('INFO', 'anything');
    }

    public function test_initialized_logTwice_DoesNotInitializeTwice()
    {
        $this->writer = new Graylog('127.0.0.1', 12201, 8196, null, $this->factory);
        $this->factory->method('createUdpTransport')->willReturn($this->transport);
        $this->factory->method('createPublisher')->willReturn($this->publisher);

        $this->factory->expects($this->once())->method('createLogger')->willReturn($this->logger);

        $this->writer->log('INFO', 'anything');
        $this->writer->log('INFO', 'anything');
    }
}