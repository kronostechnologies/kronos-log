<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy;
use Kronos\Log\Factory\Writer As WriterFactory;
use Override;

class Console extends AbstractWriter
{

    const FORCE_ANSI_COLOR = 'forceAnsiColor';
    const FORCE_NO_ANSI_COLOR = 'forceNoAnsiColor';

    /**
     * @var WriterFactory
     */
    private $factory;

    /**
     * @var ExceptionTraceHelper
     */
    private $exceptionTraceHelper;

    public function __construct(WriterFactory $factory = null, ExceptionTraceHelper $exceptionTraceHelper = null)
    {
        $this->factory = $factory ?: new WriterFactory();
        $this->exceptionTraceHelper = $exceptionTraceHelper ?: new ExceptionTraceHelper();
    }

    /**
     * @param array $settings
     * @return \Kronos\Log\Writer\Console
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
