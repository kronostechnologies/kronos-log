<?php

namespace Kronos\Log\Factory\Writer;

use Kronos\Log\Writer\AbstractWriter;

abstract class AbstractWriterFactory implements WriterFactory
{
    const string MIN_LEVEL = 'minLevel';
    const string MAX_LEVEL = 'maxLevel';

    protected function setCommonSettings(AbstractWriter $writer, array $settings): void
    {
        if (isset($settings[self::MIN_LEVEL])) {
            $writer->setMinLevel($settings[self::MIN_LEVEL]);
        }

        if (isset($settings[self::MAX_LEVEL])) {
            $writer->setMaxLevel($settings[self::MAX_LEVEL]);
        }
    }
}
