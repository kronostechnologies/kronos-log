<?php

namespace Kronos\Log\Factory\Writer;

use Fluent\Logger\FluentLogger;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Writer\FluentdWriter;
use Override;

class FluentdWriterFactory extends AbstractWriterFactory
{
    const string APPLICATION = 'application';
    const string TAG = 'tag';
    const string HOSTNAME = 'hostname';
    const string PORT = 'port';
    const string WRAP_CONTEXT_IN_META = 'wrapContextInMeta';
    const string FLUENT_BIT = 'fluentBit';

    private ExceptionTraceHelper $exceptionTraceHelper;

    public function __construct(?ExceptionTraceHelper $exceptionTraceHelper = null)
    {
        $this->exceptionTraceHelper = $exceptionTraceHelper ?: new ExceptionTraceHelper();
    }

    /**
     * @throws RequiredSetting
     */
    #[Override]
    public function createFromArray(array $settings): FluentdWriter
    {
        $this->checkRequiredSettings($settings);

        $hostname = $settings[self::HOSTNAME];
        $port = isset($settings[self::PORT]) ? (int)$settings[self::PORT] : FluentLogger::DEFAULT_LISTEN_PORT;
        $application = isset($settings[self::APPLICATION]) ? $settings[self::APPLICATION] : null;
        $tag = $settings[self::TAG];
        $wrapContextInMeta = isset($settings[self::WRAP_CONTEXT_IN_META])
            ? filter_var($settings[self::WRAP_CONTEXT_IN_META], FILTER_VALIDATE_BOOLEAN)
            : false;
        $fluentBit = isset($settings[self::FLUENT_BIT])
            ? filter_var($settings[self::FLUENT_BIT], FILTER_VALIDATE_BOOLEAN)
            : false;

        $writer = new FluentdWriter(
            $hostname,
            $port,
            $tag,
            $application,
            $wrapContextInMeta,
            null,
            null,
            $fluentBit
        );

        $exceptionTraceBuilder = $this->exceptionTraceHelper->getExceptionTraceBuilderForSettings($settings);
        $writer->setExceptionTraceBuilder($exceptionTraceBuilder);

        $previousExceptionTraceBuilder = $this->exceptionTraceHelper->getPreviousExceptionTraceBuilderForSettings($settings);
        $writer->setPreviousExceptionTraceBuilder($previousExceptionTraceBuilder);

        $this->setCommonSettings($writer, $settings);

        return $writer;
    }

    /**
     * @param array $settings
     * @throws RequiredSetting
     */
    private function checkRequiredSettings(array $settings): void
    {
        $this->throwIfMissing($settings, self::HOSTNAME);
        $this->throwIfMissing($settings, self::TAG);
    }

    /**
     * @param $settings
     * @param $index
     * @throws RequiredSetting
     */
    private function throwIfMissing($settings, $index): void
    {
        if (!isset($settings[$index])) {
            throw new RequiredSetting($index . ' setting is required');
        }
    }
}
