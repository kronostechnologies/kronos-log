<?php

namespace Kronos\Log\Factory\Writer;

use Kronos\Log\Writer\TriggerErrorWriter;
use Override;

class TriggerErrorWriterFactory extends AbstractWriterFactory
{
    public function create(): TriggerErrorWriter
    {
        return new TriggerErrorWriter();
    }

    #[Override]
    public function createFromArray(array $settings): TriggerErrorWriter
    {
        $writer = $this->create();
        $this->setCommonSettings($writer, $settings);
        return $writer;
    }
}
