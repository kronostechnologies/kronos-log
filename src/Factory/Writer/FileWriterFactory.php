<?php

namespace Kronos\Log\Factory\Writer;

use Kronos\Log\Adaptor\FileAdaptorFactory;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Formatter\ContextStringifier;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Writer\FileWriter;
use Override;

class FileWriterFactory extends AbstractWriterFactory
{
    const string FILENAME = 'filename';

    private ExceptionTraceHelper $exceptionTraceHelper;
    private FileAdaptorFactory $fileAdaptorFactory;
    private ContextStringifier $contextStringifier;

    public function __construct(
        ?FileAdaptorFactory $fileAdaptorFactory = null,
        ?ExceptionTraceHelper $exceptionTraceHelper = null,
        ?ContextStringifier $contextStringifier = null
    ) {
        $this->fileAdaptorFactory = $fileAdaptorFactory ?? new FileAdaptorFactory();
        $this->exceptionTraceHelper = $exceptionTraceHelper ?? new ExceptionTraceHelper();
        $this->contextStringifier = $contextStringifier ?? new ContextStringifier();
    }

    public function create(
        ?string $filename,
        ?TraceBuilder $exceptionTraceBuilder = null,
        ?TraceBuilder $previousExceptionTraceBuilder = null
    ): FileWriter {
        $writer = new FileWriter(
            $filename,
            $this->fileAdaptorFactory,
            $exceptionTraceBuilder,
            $previousExceptionTraceBuilder
        );
        $writer->setPrependDateTime();
        $writer->setPrependLogLevel();
        $writer->setContextStringifier($this->contextStringifier);
        return $writer;
    }

    /**
     * @param array $settings
     * @psalm-suppress MoreSpecificReturnType
     * @return FileWriter
     * @throws RequiredSetting
     */
    #[Override]
    public function createFromArray(array $settings): FileWriter
    {
        if (!isset($settings[self::FILENAME])) {
            throw new RequiredSetting(self::FILENAME . ' setting is required');
        }

        $exceptionTraceBuilder = $this->exceptionTraceHelper->getExceptionTraceBuilderForSettings($settings);
        $previousExceptionTraceBuilder = $this->exceptionTraceHelper->getPreviousExceptionTraceBuilderForSettings($settings);

        $writer = $this->create(
            $settings[self::FILENAME],
            $exceptionTraceBuilder,
            $previousExceptionTraceBuilder
        );

        $this->setCommonSettings($writer, $settings);
        return $writer;
    }
}
