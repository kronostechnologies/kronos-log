<?php

namespace Kronos\Log;

use Psr\Log\LogLevel;

/**
 * Class SettingsFormatter
 * @package Kronos\Log
 */
class SettingsFormatter
{

    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var array
     */
    private $writerSpecificChanges = [];

    /**
     * @var array
     */
    private $globalChanges = [];

    /**
     * @var array
     */
    private $flags = [];

    /**
     * @var array
     */
    private $defaults = [];

    const WRITER_TYPE = 'type';
    const WRITER_SETTINGS = 'settings';
    const TO_DELETE = 'to_delete';
    const ACTIVATE_WITH_FLAG = 'activateWithFlag';
    const DEACTIVATE_WITH_FLAG = 'deactivateWithFlag';

    /**
     * SettingsFormatter constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * Sets the writer specific settings to change, using an array of the format
     *
     * ['type' => [
     *        'settingName' => 'setting value'
     *      ]
     * ]
     *
     * @param array $writerSpecificChanges
     */
    public function setWriterSpecificChanges(array $writerSpecificChanges)
    {
        $this->writerSpecificChanges = $writerSpecificChanges;
    }

    /**
     * Change settings for all writers
     *
     * ['settingName' => 'setting value']
     *
     * @param array $globalChanges
     */
    public function setGlobalChanges(array $globalChanges)
    {
        $this->globalChanges = $globalChanges;
    }

    /**
     * Default writer settings if they are not speficifed
     * @param array $defaults
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;
    }

    /**
     * @param array $flags
     */
    public function setFlags(array $flags)
    {
        $this->flags = $flags;
    }

    /**
     * Returns a formatted array of settings for the log writer builder.
     *
     * @return array
     */
    public function getFormattedSettings()
    {
        $settings = $this->filterSettings($this->settings);
        $settings = $this->applyGlobalChanges($settings);
        $settings = $this->applySpecificChanges($settings);
        return $this->applyDefaults($settings);
    }

    /**
     * Compares active and inactive flags with allowed and unallowed flags in each of the writers' config.
     *
     * @param $settings
     * @return mixed
     */
    private function filterSettings($settings)
    {
        foreach ($settings as $index => $writer) {
            $activationFlags = $this->getActivationFlags($writer);
            $deactivationFlags = $this->getDeactivationFlags($writer);

            if (!empty($deactivationFlags) && $this->isAtLeastOneFlagInConfig($this->flags, $deactivationFlags)) {
                unset($settings[$index]);
            } elseif (!empty($activationFlags) && $this->noFlagsAreInConfig($this->flags, $activationFlags)) {
                unset($settings[$index]);
            }
        }

        return $settings;
    }

    public function applyGlobalChanges($settings)
    {
        foreach ($settings as $index => $writer) {
            foreach ($this->globalChanges as $settingName => $settingValue) {
                if (!isset($writer[self::WRITER_SETTINGS])) {
                    $settings[$index][self::WRITER_SETTINGS] = [];
                }

                $settings[$index][self::WRITER_SETTINGS][$settingName] = $settingValue;
            }
        }

        return $settings;
    }

    public function applySpecificChanges($settings)
    {
        foreach ($settings as $index => $writer) {
            foreach ($this->writerSpecificChanges as $writerType => $changes) {
                if ($writer[self::WRITER_TYPE] == $writerType) {
                    foreach ($changes as $settingName => $settingValue) {
                        $settings[$index][self::WRITER_SETTINGS][$settingName] = $settingValue;
                    }
                }
            }
        }

        return $settings;
    }

    public function applyDefaults($settings)
    {
        foreach ($settings as $index => $writer) {
            foreach ($this->defaults as $settingName => $settingValue) {
                if (isset($writer[self::WRITER_SETTINGS]) && !isset($settings[$index][self::WRITER_SETTINGS][$settingName])) {
                    $settings[$index][self::WRITER_SETTINGS][$settingName] = $settingValue;
                }
            }
        }

        return $settings;
    }

    /**
     * Checks if at least one of the active flags is in the activate/deactivate flags array
     *
     * @param $activeFlags
     * @param $config
     * @return bool
     */
    private function isAtLeastOneFlagInConfig($activeFlags, $config)
    {
        return count(array_intersect($activeFlags, $config)) > 0;
    }

    /**
     * Checks if none of the active flags is in the activate/deactivate flags array
     *
     * @param $activeFlags
     * @param $config
     * @return bool
     */
    private function noFlagsAreInConfig($activeFlags, $config)
    {
        return count(array_intersect($activeFlags, $config)) == 0;
    }

    /**
     * @param $writer
     * @return array
     */
    private function getActivationFlags($writer)
    {
        if (isset($writer[self::WRITER_SETTINGS]) && isset($writer[self::WRITER_SETTINGS][self::ACTIVATE_WITH_FLAG]) && is_array($writer[self::WRITER_SETTINGS][self::ACTIVATE_WITH_FLAG])) {
            return $writer[self::WRITER_SETTINGS][self::ACTIVATE_WITH_FLAG];
        } else {
            return [];
        }
    }

    /**
     * @param $writer
     * @return array
     */
    private function getDeactivationFlags($writer)
    {
        if (isset($writer[self::WRITER_SETTINGS]) && isset($writer[self::WRITER_SETTINGS][self::DEACTIVATE_WITH_FLAG]) && is_array($writer[self::WRITER_SETTINGS][self::DEACTIVATE_WITH_FLAG])) {
            return $writer[self::WRITER_SETTINGS][self::DEACTIVATE_WITH_FLAG];
        } else {
            return [];
        }
    }
}