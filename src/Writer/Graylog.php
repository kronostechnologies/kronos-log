<?php


namespace Kronos\Log\Writer;


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
        throw new \Exception("Not implemented");
    }
}