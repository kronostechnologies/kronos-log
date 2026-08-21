<?php

namespace Kronos\Log\Factory\Writer;

use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Writer\SyslogWriter;
use Override;

class SyslogWriterFactory extends AbstractWriterFactory
{
    const string APPLICATION = 'application';
    const string OPTION = 'option';
    const string FACILITY = 'facility';

    public function create(string $application, int $option = LOG_ODELAY, int $facility = LOG_LOCAL0): SyslogWriter
    {
        return new SyslogWriter($application, $option, $facility);
    }

    /**
     * @param array $settings
     * @return SyslogWriter
     * @throws RequiredSetting
     */
    #[Override]
    public function createFromArray(array $settings): SyslogWriter
    {
        if (!isset($settings[self::APPLICATION])) {
            throw new RequiredSetting(self::APPLICATION . ' setting is required');
        }

        $writer = $this->create(
            $settings[self::APPLICATION],
            $this->getOption($settings),
            $this->getFacility($settings)
        );

        $this->setCommonSettings($writer, $settings);

        return $writer;
    }

    /**
     * @param array $settings
     * @return int
     */
    protected function getOption(array $settings)
    {
        return isset($settings[self::OPTION]) ? $settings[self::OPTION] : SyslogWriter::DEFAULT_OPTION;
    }

    /**
     * @param array $settings
     * @return int
     */
    protected function getFacility(array $settings)
    {
        return isset($settings[self::FACILITY]) ? $settings[self::FACILITY] : SyslogWriter::DEFAULT_FACILITY;
    }
}
