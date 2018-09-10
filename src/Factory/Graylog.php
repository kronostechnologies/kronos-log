<?php


namespace Kronos\Log\Factory;


use Gelf\Logger as GraylogLogger;
use Gelf\Publisher;
use Gelf\PublisherInterface;
use Gelf\Transport\AbstractTransport;
use Gelf\Transport\UdpTransport;

class Graylog
{
    /**
     * @param string $hostname
     * @param int $port
     * @param int $chunkSize
     * @return UdpTransport
     */
    public function createUdpTransport($hostname, $port, $chunkSize)
    {
        $hostname = \gethostbyname($hostname);

        return new UdpTransport($hostname, $port, $chunkSize);
    }

    /**
     * @param AbstractTransport $transport
     * @return Publisher
     */
    public function createPublisher(AbstractTransport $transport)
    {
        return new Publisher($transport);
    }

    /**
     * @param PublisherInterface $publisher
     * @return GraylogLogger
     */
    public function createLogger(PublisherInterface $publisher)
    {
        return new GraylogLogger($publisher);
    }
}