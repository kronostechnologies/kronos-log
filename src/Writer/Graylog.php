<?php


namespace Kronos\Log\Writer;


use Gelf\Logger;
use Kronos\Log\AbstractWriter;
use Psr\Log\LogLevel;

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
     * @var bool
     */
    protected $outputVerboseLevel;

    /**
     * @var \Kronos\Log\Factory\Graylog
     */
    protected $factory;

    const LEVEL_MAPPINGS = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT => 1,
        LogLevel::CRITICAL => 2,
        LogLevel::ERROR => 3,
        LogLevel::WARNING => 4,
        LogLevel::NOTICE => 5,
        LogLevel::INFO => 6,
        LogLevel::DEBUG => 7,
    ];

    /**
     * @param string $hostname
     * @param int $port
     * @param int $chunkSize
     * @param null|string $application
     * @param bool $outputVerboseLevel
     * @param \Kronos\Log\Factory\Graylog|null $factory
     */
    public function __construct($hostname, $port, $chunkSize, $application, $outputVerboseLevel, \Kronos\Log\Factory\Graylog $factory = null)
    {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->chunkSize = $chunkSize;
        $this->application = $application;
        $this->outputVerboseLevel = $outputVerboseLevel;
        $this->factory = $factory ?: new \Kronos\Log\Factory\Graylog();
    }

    public function log($level, $message, array $context = [])
    {
        try {
            $logger = $this->initializeLogger();

            if ($this->application !== null) {
                $context['_app'] = $this->application;
            }

            if ($this->outputVerboseLevel) {
                $context['levelVerbose'] = $level;
            }

            $logger->log(self::LEVEL_MAPPINGS[$level], $message, $context);
            return true;
        } catch (\Exception $ex) {
            trigger_error('An error occurred while writing with the Graylog writer: ' . $ex->getMessage(), E_USER_WARNING);
            return false;
        }
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

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return int
     */
    public function getChunkSize()
    {
        return $this->chunkSize;
    }

    /**
     * @return null|string
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @return bool
     */
    public function getOutputVerboseLevel()
    {
        return $this->outputVerboseLevel;
    }
}