<?php


namespace Kronos\Log\Builder\Strategy;


use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\WriterInterface;

class Graylog extends AbstractWriter
{
    const APPLICATION = 'application';
    const HOSTNAME = 'hostname';
    const PORT = 'port';
    const CHUNK_SIZE = 'chunkSize';
    const OUTPUT_VERBOSE_LEVEL = 'outputVerboseLevel';

    /**
     * @param array $settings
     * @return WriterInterface|\Kronos\Log\Writer\Graylog
     * @throws RequiredSetting
     */
    public function buildFromArray(array $settings)
    {
        $this->checkRequiredSettings($settings);

        $hostname = $settings[self::HOSTNAME];
        $port = isset($settings[self::PORT]) ? $settings[self::PORT] : 12201;
        $chunkSize = isset($settings[self::CHUNK_SIZE]) ? $settings[self::CHUNK_SIZE] : 0;
        $application = isset($settings[self::APPLICATION]) ? $settings[self::APPLICATION] : null;
        $outputVerboseLevel = isset($settings[self::OUTPUT_VERBOSE_LEVEL]) ? $settings[self::OUTPUT_VERBOSE_LEVEL] : false;

        return new \Kronos\Log\Writer\Graylog(
            $hostname,
            $port,
            $chunkSize,
            $application,
            $outputVerboseLevel
        );
    }

    /**
     * @param array $settings
     * @throws RequiredSetting
     */
    private function checkRequiredSettings(array $settings)
    {
        $this->throwIfMissing($settings, self::HOSTNAME);
        $this->throwIfMissing($settings, self::CHUNK_SIZE);
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
}
