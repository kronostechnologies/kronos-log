<?php


namespace Kronos\Log\Writer;


use Gelf\Logger;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;
use Kronos\Log\AbstractWriter;

class Graylog extends AbstractWriter
{
    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var int
     */
    protected $chunkSize;

    /**
     * @var string|null
     */
    protected $application;

    /**
     * @var Logger|null
     */
    protected $logger;

    /**
     * @var \Kronos\Log\Factory\Graylog
     */
    protected $factory;

    /**
     * @param string $hostname
     * @param int $port
     * @param int $chunkSize
     * @param null|string $application
     */
    public function __construct($hostname, $port, $chunkSize, $application, \Kronos\Log\Factory\Graylog $factory = null)
    {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->chunkSize = $chunkSize;
        $this->application = $application;
        $this->factory = $factory ?: new \Kronos\Log\Factory\Graylog();
    }

    public function log($level, $message, array $context = [])
    {
        $logger = $this->initializeLogger();

        throw new \Exception("Not implemented");
    }

    /**
     * @param bool $force
     * @return Logger
     */
    protected function initializeLogger($force = false)
    {
        if ($force || $this->logger === null) {
            $hostname = \gethostbyname($this->hostname);

            $transport = $this->factory->createUdpTransport($hostname, $this->port, $this->chunkSize);
            $publisher = $this->factory->createPublisher($transport);

            $this->logger = $this->factory->createLogger($publisher);
        }

        return $this->logger;
    }
}