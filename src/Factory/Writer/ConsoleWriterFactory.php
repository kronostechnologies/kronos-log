<?php

namespace Kronos\Log\Factory\Writer;

use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Writer\ConsoleWriter;
use Override;

class ConsoleWriterFactory extends AbstractWriterFactory
{
    const string FORCE_ANSI_COLOR = 'forceAnsiColor';
    const string FORCE_NO_ANSI_COLOR = 'forceNoAnsiColor';

    private ExceptionTraceHelper $exceptionTraceHelper;

    public function __construct(?ExceptionTraceHelper $exceptionTraceHelper = null)
    {
        $this->exceptionTraceHelper = $exceptionTraceHelper ?: new ExceptionTraceHelper();
    }

    public function create(
        ?TraceBuilder $exceptionTraceBuilder = null,
        ?TraceBuilder $previousExceptionTraceBuilder = null
    ): ConsoleWriter {
        $writer = new ConsoleWriter($exceptionTraceBuilder, $previousExceptionTraceBuilder);
        $writer->setPrependDateTime();
        $writer->setPrependLogLevel();
        return $writer;
    }

    /**
     * @param array $settings
     * @psalm-suppress MoreSpecificReturnType
     * @return ConsoleWriter
     */
    #[Override]
    public function createFromArray(array $settings): ConsoleWriter
    {
        $exceptionTraceBuilder = $this->exceptionTraceHelper->getExceptionTraceBuilderForSettings($settings);
        $previousExceptionTraceBuilder = $this->exceptionTraceHelper->getPreviousExceptionTraceBuilderForSettings($settings);

        $writer = $this->create($exceptionTraceBuilder, $previousExceptionTraceBuilder);

        $this->setCommonSettings($writer, $settings);

        if (isset($settings['forceAnsiColor']) && $settings['forceAnsiColor']) {
            $writer->setForceAnsiColorSupport();
        }
        if (isset($settings['forceNoAnsiColor']) && $settings['forceNoAnsiColor']) {
            $writer->setForceNoAnsiColorSupport();
        }

        return $writer;
    }
}
