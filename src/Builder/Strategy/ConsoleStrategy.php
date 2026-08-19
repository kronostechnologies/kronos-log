<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Factory\WriterFactory;
use Override;

class ConsoleStrategy extends AbstractWriterStrategy
{
    const string FORCE_ANSI_COLOR = 'forceAnsiColor';
    const string FORCE_NO_ANSI_COLOR = 'forceNoAnsiColor';

    private WriterFactory $factory;
    private ExceptionTraceHelper $exceptionTraceHelper;

    public function __construct(?WriterFactory $factory = null, ?ExceptionTraceHelper $exceptionTraceHelper = null)
    {
        $this->factory = $factory ?: new WriterFactory();
        $this->exceptionTraceHelper = $exceptionTraceHelper ?: new ExceptionTraceHelper();
    }

    /**
     * @param array $settings
     * @psalm-suppress MoreSpecificReturnType
     * @return \Kronos\Log\Writer\ConsoleWriter
     */
    #[Override]
    public function buildFromArray(array $settings)
    {
        $exceptionTraceBuilder = $this->exceptionTraceHelper->getExceptionTraceBuilderForSettings($settings);
        $previousExceptionTraceBuilder = $this->exceptionTraceHelper->getPreviousExceptionTraceBuilderForSettings($settings);

        $writer = $this->factory->createConsoleWriter($exceptionTraceBuilder, $previousExceptionTraceBuilder);

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
