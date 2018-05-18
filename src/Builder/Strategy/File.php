<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\Writer As WriterFactory;

class File extends AbstractWriter
{
    const FILENAME = 'filename';

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
        $this->factory = is_null($factory) ? new WriterFactory() : $factory;
        $this->exceptionTraceHelper = $exceptionTraceHelper ?: new ExceptionTraceHelper();
    }

    /**
     * @param array $settings
     * @return \Kronos\Log\Writer\File
     * @throws RequiredSetting
     */
    public function buildFromArray(array $settings)
    {
        if (!isset($settings[self::FILENAME])) {
            throw new RequiredSetting(self::FILENAME . ' setting is required');
        }

        $exceptionTraceBuilder = $this->exceptionTraceHelper->getExceptionTraceBuilderForSettings($settings);
        $previousExceptionTraceBuilder = $this->exceptionTraceHelper->getPreviousExceptionTraceBuilderForSettings($settings);

        $writer = $this->factory->createFileWriter($settings[self::FILENAME], $exceptionTraceBuilder, $previousExceptionTraceBuilder);

        $this->setCommonSettings($writer, $settings);

        return $writer;
    }
}