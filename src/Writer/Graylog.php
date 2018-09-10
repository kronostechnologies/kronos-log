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
     * @param string $hostname
     * @param int $port
     * @param int $chunkSize
     * @param null|string $application
     */
    public function __construct($hostname, $port, $chunkSize, $application)
    {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->chunkSize = $chunkSize;
        $this->application = $application;
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

            $transport = new UdpTransport($hostname, $this->port, $this->chunkSize);
            $publisher = new Publisher($transport);

            $this->logger = new Logger($publisher);
        }

        return $this->logger;
    }
}