<?php


namespace Kronos\Log\Writer;


use Fluent\Logger\FluentLogger;
use Kronos\Log\AbstractWriter;

class Fluentd extends AbstractWriter
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
     * @var string
     */
    protected $tag;

    /**
     * @var string|null
     */
    protected $application;

    /**
     * @var FluentLogger|null
     */
    protected $logger;

    /**
     * @param string $hostname
     * @param int $port
     * @param $tag
     * @param null|string $application
     */
    public function __construct($hostname, $port, $tag, $application)
    {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->tag = $tag;
        $this->application = $application;
    }

    public function log($level, $message, array $context = [])
    {
        try {
             $logger = $this->initializeLogger();

            $context['level'] = $level;
            if ($this->application !== null) {
                $context['_app'] = $this->application;
            }
            $context['message'] = $message;

            $logger->post($this->tag, $context);

            return true;
        } catch (\Exception $ex) {
            trigger_error('An error occurred while writing with the Fluentd writer: ' . $ex->getMessage(), E_USER_WARNING);
            return false;
        }
    }

    /**
     * @return FluentLogger
     */
    protected function initializeLogger()
    {
        if ($this->logger === null) {
            $this->logger = new FluentLogger($this->hostname, $this->port);
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
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return null|string
     */
    public function getApplication()
    {
        return $this->application;
    }
}