<?php


namespace Kronos\Log\Writer;


use Fluent\Logger\FluentLogger;
use Kronos\Log\AbstractWriter;
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
     * @var \Kronos\Log\Factory\Fluentd|null
     */
    protected $factory;

    /**
     * @var boolean
     */
    protected $wrapContextInMeta;

    /**
     * @var TraceBuilder
     */
    private $exceptionTraceBuilder;

    /**
     * @var TraceBuilder
     */
    private $previousExceptionTraceBuilder;

    /**
     * @var ContextStringifier
     */
    private $contextStringifier;

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
        ContextStringifier $contextStringifier = null
    ) {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->tag = $tag;
        $this->application = $application;
        $this->wrapContextInMeta = $wrapContextInMeta;
        $this->factory = $factory ?: new \Kronos\Log\Factory\Fluentd();
        $this->contextStringifier = $contextStringifier ?: new ContextStringifier();
    }

    public function log($level, $message, array $context = [])
    {
        try {
            $logger = $this->initializeLogger();

            $context = $this->processContext($context);

            $context['level'] = $level;
            if ($this->application !== null) {
                $context['_app'] = $this->application;
            }
            $context['message'] = $this->interpolate($message, $context);

            if ($this->wrapContextInMeta) {
                $context['meta'] = $context;

                unset($context['meta']['level']);
                unset($context['meta']['message']);
            }

            $logger->post($this->tag, $context);

            return true;
        } catch (\Exception $ex) {
            trigger_error('An error occurred while writing with the Fluentd writer: ' . $ex->getMessage(),
                E_USER_WARNING);
            return false;
        }
    }

    private function processContext(array $context){
        $context = $this->replaceException($context);
        return $this->contextStringifier->stringifyArray($context);
    }

    /**
     * @return FluentLogger
     */
    protected function initializeLogger()
    {
        if ($this->logger === null) {
            $this->logger = $this->factory->createFluentLogger($this->hostname, $this->port);
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
     * @return TraceBuilder
     */
    public function getExceptionTraceBuilder()
    {
        return $this->exceptionTraceBuilder;
    }

    /**
     * @param TraceBuilder $exceptionTraceBuilder
     */
    public function setExceptionTraceBuilder($exceptionTraceBuilder)
    {
        $this->exceptionTraceBuilder = $exceptionTraceBuilder;
    }

    /**
     * @return TraceBuilder
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
