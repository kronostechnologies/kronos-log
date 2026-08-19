<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\WriterFactory As WriterFactory;
use Override;

class SyslogStrategy extends AbstractWriterStrategy
{

    const string APPLICATION = 'application';
    const string OPTION = 'option';
    const string FACILITY = 'facility';

    private WriterFactory $factory;

    public function __construct(?WriterFactory $factory = null)
    {
        $this->factory = is_null($factory) ? new WriterFactory() : $factory;
    }

    /**
     * @param array $settings
     * @return \Kronos\Log\Writer\SyslogWriter
     * @throws RequiredSetting
     */
    #[Override]
    public function buildFromArray(array $settings)
    {
        if (!isset($settings[self::APPLICATION])) {
            throw new RequiredSetting(self::APPLICATION . ' setting is required');
        }

        $writer = $this->factory->createSyslogWriter(
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
        return isset($settings[self::OPTION]) ? $settings[self::OPTION] : \Kronos\Log\Writer\SyslogWriter::DEFAULT_OPTION;
    }

    /**
     * @param array $settings
     * @return int
     */
    protected function getFacility(array $settings)
    {
        return isset($settings[self::FACILITY]) ? $settings[self::FACILITY] : \Kronos\Log\Writer\SyslogWriter::DEFAULT_FACILITY;
    }
}
