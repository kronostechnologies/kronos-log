<?php


namespace Kronos\Log\Writer;


use Fluent\Logger\FluentLogger;
use Kronos\Log\AbstractWriter;
use Kronos\Log\Factory\Fluentd\FluentBitJsonPacker;
use Kronos\Log\Formatter\ContextStringifier;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Traits\ExceptionTraceBuilderAwareTrait;

class Fluentd extends AbstractWriter
{
    use ExceptionTraceBuilderAwareTrait;

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
     * @var \Kronos\Log\Factory\Fluentd
     */
    protected $factory;

    /**
     * @var boolean
     */
    protected $wrapContextInMeta;

    /**
     * @var TraceBuilder|null
     */
    private $exceptionTraceBuilder;

    /**
     * @var TraceBuilder|null
     */
    private $previousExceptionTraceBuilder;

    /**
     * @var ContextStringifier
     */
    private $contextStringifier;

    /**
     * @var bool
     */
    private $fluentBit;

    /**
     * @param string $hostname
     * @param int $port
     * @param $tag
     * @param null|string $application
     * @param bool $wrapContextInMeta
     * @param \Kronos\Log\Factory\Fluentd|null $factory
     * @param ContextStringifier|null $contextStringifier
     */
    public function __construct(
        $hostname,
        $port,
        $tag,
        $application,
        $wrapContextInMeta,
        \Kronos\Log\Factory\Fluentd $factory = null,
        ContextStringifier $contextStringifier = null,
        $fluentBit = false
    ) {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->tag = $tag;
        $this->application = $application;
        $this->wrapContextInMeta = $wrapContextInMeta;
        $this->factory = $factory ?: new \Kronos\Log\Factory\Fluentd();
        $this->contextStringifier = $contextStringifier ?: new ContextStringifier();
        $this->fluentBit = $fluentBit;
    }

    public function log($level, $message, array $context = [])
    {
        try {
            $logger = $this->initializeLogger();

            $data = $this->processContext($context);
            $data['level'] = $level;
            if ($this->application !== null) {
                $data['_app'] = $this->application;
            }
            $data['message'] = $this->interpolate($message, $context);

            $logger->post($this->tag, $data);

            return true;
        } catch (\Exception $ex) {
            trigger_error('An error occurred while writing with the Fluentd writer: ' . $ex->getMessage(),
                E_USER_WARNING);
            return false;
        }
    }

    private function processContext(array $context)
    {
        $context = $this->replaceException($context);
        $context = $this->contextStringifier->stringifyArray($context);
        if ($this->wrapContextInMeta) {
            return [
                'meta' => $context
            ];
        } else {
            return $context;
        }
    }

    /**
     * @return FluentLogger
     */
    protected function initializeLogger()
    {
        if ($this->logger === null) {
            $packer = null;
            if ($this->fluentBit === true) {
                $packer = new FluentBitJsonPacker();
            }
            $this->logger = $this->factory->createFluentLogger($this->hostname, $this->port, [], $packer);
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

    /**
     * @return bool
     */
    public function willWrapContextInMeta()
    {
        return $this->wrapContextInMeta;
    }

    /**
     * @return bool
     */
    public function getFluentBit()
    {
        return $this->fluentBit;
    }

    /**
     * @param bool $value
     */
    public function setFluentBit($value)
    {
        $this->fluentBit = $value;
    }

    /**
     * @return ContextStringifier
     */
    public function getContextStringifier()
    {
        return $this->contextStringifier;
    }

    /**
     * @param ContextStringifier $contextStringifier
     */
    public function setContextStringifier(ContextStringifier $contextStringifier)
    {
        $this->contextStringifier = $contextStringifier;
    }

    /**
     * @return TraceBuilder|null
     */
    public function getExceptionTraceBuilder()
    {
        return $this->exceptionTraceBuilder;
    }

    /**
     * @param TraceBuilder|null $exceptionTraceBuilder
     */
    public function setExceptionTraceBuilder($exceptionTraceBuilder)
    {
        $this->exceptionTraceBuilder = $exceptionTraceBuilder;
    }

    /**
     * @return TraceBuilder|null
     */
    public function getPreviousExceptionTraceBuilder()
    {
        return $this->previousExceptionTraceBuilder;
    }

    /**
     * @param TraceBuilder $previousExceptionTraceBuilder
     */
    public function setPreviousExceptionTraceBuilder($previousExceptionTraceBuilder)
    {
        $this->previousExceptionTraceBuilder = $previousExceptionTraceBuilder;
    }

}
