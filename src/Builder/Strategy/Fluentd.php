<?php


namespace Kronos\Log\Builder\Strategy;


use Fluent\Logger\FluentLogger;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\WriterInterface;

class Fluentd extends AbstractWriter
{
    const APPLICATION = 'application';
    const TAG = 'tag';
    const HOSTNAME = 'hostname';
    const PORT = 'port';
    const WRAP_CONTEXT_IN_META = 'wrapContextInMeta';

    /**
     * @param array $settings
     * @return WriterInterface|\Kronos\Log\Writer\Fluentd
     * @throws RequiredSetting
     */
    public function buildFromArray(array $settings)
    {
        $this->checkRequiredSettings($settings);

        $hostname = $settings[self::HOSTNAME];
        $port = $settings[self::PORT] ?: FluentLogger::DEFAULT_LISTEN_PORT;
        $application = $settings[self::APPLICATION];
        $tag = $settings[self::TAG];
        $wrapContextInMeta = filter_var($settings[self::WRAP_CONTEXT_IN_META], FILTER_VALIDATE_BOOLEAN);

        return new \Kronos\Log\Writer\Fluentd(
            $hostname,
            $port,
            $tag,
            $application,
            $wrapContextInMeta
        );
    }

    /**
     * @param array $settings
     * @throws RequiredSetting
     */
    private function checkRequiredSettings(array $settings)
    {
        $this->throwIfMissing($settings, self::HOSTNAME);
        $this->throwIfMissing($settings, self::TAG);
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