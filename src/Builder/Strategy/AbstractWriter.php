<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy;
use Psr\Log\LogLevel;

abstract class AbstractWriter implements Strategy
{

    const MIN_LEVEL = 'minLevel';
    const MAX_LEVEL = 'maxLevel';

    /**
     * @param \Kronos\Log\AbstractWriter $writer
     * @param array $settings Writer settings
     */
    protected function setCommonSettings(\Kronos\Log\AbstractWriter $writer, array $settings)
    {
        if (isset($settings[self::MIN_LEVEL])) {
            $writer->setMinLevel($settings[self::MIN_LEVEL]);
        }

        if (isset($settings[self::MAX_LEVEL])) {
            $writer->setMaxLevel($settings[self::MAX_LEVEL]);
        }
    }
}