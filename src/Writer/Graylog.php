<?php


namespace Kronos\Log\Writer;


use Gelf\Logger;
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
     * @param \Kronos\Log\Factory\Graylog|null $factory
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


    }

    /**
     * @param bool $force
     * @return Logger
     */
    protected function initializeLogger()
    {
        if ($this->logger === null) {
            $transport = $this->factory->createUdpTransport($this->hostname, $this->port, $this->chunkSize);
            $publisher = $this->factory->createPublisher($transport);

            $this->logger = $this->factory->createLogger($publisher);
        }

        return $this->logger;
    }
}