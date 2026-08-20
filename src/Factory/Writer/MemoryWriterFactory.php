<?php

namespace Kronos\Log\Factory\Writer;

use Kronos\Log\Writer\MemoryWriter;
use Override;

class MemoryWriterFactory extends AbstractWriterFactory
{
    public function create(): MemoryWriter
    {
        return new MemoryWriter();
    }

    /**
     * @param array $settings
     * @return MemoryWriter
     */
    #[Override]
    public function createFromArray(array $settings): MemoryWriter
    {
        $writer = $this->create();

        $this->setCommonSettings($writer, $settings);

        return $writer;
    }
}
