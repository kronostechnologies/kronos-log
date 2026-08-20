<?php

namespace Kronos\Log\Factory\Writer;

use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Writer\LogDNAWriter;
use Override;

class LogDNAWriterFactory extends AbstractWriterFactory
{
    const string HOSTNAME = 'hostname';
    const string APPLICATION = 'application';
    const string INGESTION_KEY = 'ingestionKey';

    const string IP_ADDRESS = 'ip';
    const string MAC_ADDRESS = 'mac';

    private ExceptionTraceHelper $exceptionTraceHelper;

    public function __construct(?ExceptionTraceHelper $exceptionTraceHelper = null)
    {
        $this->exceptionTraceHelper = $exceptionTraceHelper ?: new ExceptionTraceHelper();
    }

    public function create(
        $hostname,
        $application,
        $ingestionKey,
        ?TraceBuilder $exceptionTraceBuilder = null,
        ?TraceBuilder $previousExceptionTraceBuilder = null
    ): LogDNAWriter {
        return new LogDNAWriter(
            $hostname,
            $application,
            $ingestionKey,
            [],
            null,
            $exceptionTraceBuilder,
            $previousExceptionTraceBuilder
        );
    }

    /**
     * @param array $settings
     * @return LogDNAWriter
     * @throws RequiredSetting
     */
    #[Override]
    public function createFromArray(array $settings): LogDNAWriter
    {
        $this->checkRequiredSettings($settings);

        $exceptionTraceBuilder = $this->exceptionTraceHelper->getExceptionTraceBuilderForSettings($settings);
        $previousExceptionTraceBuilder = $this->exceptionTraceHelper->getPreviousExceptionTraceBuilderForSettings($settings);

        $writer = $this->create(
            $this->getHostName($settings),
            $settings[self::APPLICATION],
            $settings[self::INGESTION_KEY],
            $exceptionTraceBuilder,
            $previousExceptionTraceBuilder
        );

        $this->setCommonSettings($writer, $settings);

        if (isset($settings[self::IP_ADDRESS])) {
            $writer->setIpAddress($settings[self::IP_ADDRESS]);
        }

        if (isset($settings[self::MAC_ADDRESS])) {
            $writer->setMacAddress($settings[self::MAC_ADDRESS]);
        }

        return $writer;
    }

    /**
     * @param array $settings
     * @throws RequiredSetting
     */
    private function checkRequiredSettings(array $settings)
    {
        $this->throwIfMissing($settings, self::INGESTION_KEY);
    }

    /**
     * @param $settings
     * @param $index
     * @throws RequiredSetting
     */
    private function throwIfMissing($settings, $index)
    {
        if (!isset($settings[$index])) {
            throw new RequiredSetting($index . ' setting is required');
        }
    }

    /**
     * Obtains the hostname from the settings array
     * if not set, we use the server hostname instead.
     *
     * @param $settings
     * @return string
     * @throws RequiredSetting
     */
    private function getHostName($settings)
    {
        if (isset($settings[self::HOSTNAME]) && $settings[self::HOSTNAME]) {
            return $settings[self::HOSTNAME];
        } else {
            $hostname = gethostname();

            if (!$hostname) {
                throw new RequiredSetting('Hostname setting is false or null. Please either specify a hostname in the config file or remove it.');
            }

            return $hostname;
        }
    }
}
