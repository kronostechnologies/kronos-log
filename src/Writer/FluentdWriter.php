<?php

namespace Kronos\Log\Writer;

use Fluent\Logger\FluentLogger;
use Kronos\Log\Factory\Fluentd\FluentBitJsonPacker;
use Kronos\Log\Factory\FluentdFactory;
use Kronos\Log\Formatter\ContextStringifier;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Traits\ExceptionTraceBuilderAwareTrait;
use Override;
use Stringable;

class FluentdWriter extends AbstractWriter
{
    use ExceptionTraceBuilderAwareTrait;

    protected string $hostname;
    protected int $port;
    protected string $tag;
    protected ?string $application;
    protected ?FluentLogger $logger = null;
    protected FluentdFactory $factory;
    protected bool $wrapContextInMeta;
    private ?TraceBuilder $exceptionTraceBuilder = null;
    private ?TraceBuilder $previousExceptionTraceBuilder = null;
    private ContextStringifier $contextStringifier;
    private bool $fluentBit;

    public function __construct(
        string $hostname,
        int $port,
        string $tag,
        ?string $application,
        bool $wrapContextInMeta,
        ?FluentdFactory $factory = null,
        ?ContextStringifier $contextStringifier = null,
        bool $fluentBit = false
    ) {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->tag = $tag;
        $this->application = $application;
        $this->wrapContextInMeta = $wrapContextInMeta;
        $this->factory = $factory ?: new FluentdFactory();
        $this->contextStringifier = $contextStringifier ?: new ContextStringifier();
        $this->fluentBit = $fluentBit;
    }

    #[Override]
    public function log(string $level, string | Stringable $message, array $context = []): void
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
        } catch (\Exception $ex) {
            trigger_error('An error occurred while writing with the Fluentd writer: ' . $ex->getMessage(),
                E_USER_WARNING);
        }
    }

    private function processContext(array $context): array
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

    protected function initializeLogger(): FluentLogger
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

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getApplication(): ?string
    {
        return $this->application;
    }

    public function willWrapContextInMeta(): bool
    {
        return $this->wrapContextInMeta;
    }

    public function getFluentBit(): bool
    {
        return $this->fluentBit;
    }

    public function setFluentBit(bool $value): void
    {
        $this->fluentBit = $value;
    }

    public function getContextStringifier(): ContextStringifier
    {
        return $this->contextStringifier;
    }

    public function setContextStringifier(ContextStringifier $contextStringifier): void
    {
        $this->contextStringifier = $contextStringifier;
    }

    #[Override]
    public function getExceptionTraceBuilder(): ?TraceBuilder
    {
        return $this->exceptionTraceBuilder;
    }

    public function setExceptionTraceBuilder(?TraceBuilder $exceptionTraceBuilder): void
    {
        $this->exceptionTraceBuilder = $exceptionTraceBuilder;
    }

    #[Override]
    public function getPreviousExceptionTraceBuilder(): ?TraceBuilder
    {
        return $this->previousExceptionTraceBuilder;
    }

    public function setPreviousExceptionTraceBuilder(?TraceBuilder $previousExceptionTraceBuilder): void
    {
        $this->previousExceptionTraceBuilder = $previousExceptionTraceBuilder;
    }
}
