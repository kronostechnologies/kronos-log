<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Factory\WriterFactory;
use Override;

class MemoryStrategy extends AbstractWriterStrategy
{
    private WriterFactory $factory;

    public function __construct(?WriterFactory $factory = null)
    {
        $this->factory = is_null($factory) ? new WriterFactory() : $factory;
    }

    /**
     * @param array $settings
     * @return \Kronos\Log\Writer\MemoryWriter
     */
    #[Override]
    public function buildFromArray(array $settings)
    {
        $writer = $this->factory->createMemoryWriter();

        $this->setCommonSettings($writer, $settings);

        return $writer;
    }
}
