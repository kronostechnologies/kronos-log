<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\WriterFactory As WriterFactory;
use Override;

class FileStragegy extends AbstractWriterStrategy
{
    const string FILENAME = 'filename';

    private WriterFactory $factory;
    private ExceptionTraceHelper $exceptionTraceHelper;

    public function __construct(?WriterFactory $factory = null, ?ExceptionTraceHelper $exceptionTraceHelper = null)
    {
        $this->factory = is_null($factory) ? new WriterFactory() : $factory;
        $this->exceptionTraceHelper = $exceptionTraceHelper ?: new ExceptionTraceHelper();
    }

    /**
     * @param array $settings
     * @psalm-suppress MoreSpecificReturnType
     * @return \Kronos\Log\Writer\FileWriter
     * @throws RequiredSetting
     */
    #[Override]
    public function buildFromArray(array $settings)
    {
        if (!isset($settings[self::FILENAME])) {
            throw new RequiredSetting(self::FILENAME . ' setting is required');
        }

        $exceptionTraceBuilder = $this->exceptionTraceHelper->getExceptionTraceBuilderForSettings($settings);
        $previousExceptionTraceBuilder = $this->exceptionTraceHelper->getPreviousExceptionTraceBuilderForSettings($settings);

        $writer = $this->factory->createFileWriter(
            $settings[self::FILENAME],
            $exceptionTraceBuilder,
            $previousExceptionTraceBuilder
        );

        $this->setCommonSettings($writer, $settings);

        return $writer;
    }
}
