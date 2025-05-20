<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy;
use Kronos\Log\Factory\Writer As WriterFactory;
use Override;

class Memory extends AbstractWriter
{
    private WriterFactory $factory;

    public function __construct(?WriterFactory $factory = null)
    {
        $this->factory = is_null($factory) ? new WriterFactory() : $factory;
    }

    /**
     * @param array $settings
     * @return \Kronos\Log\Writer\Memory
     */
    #[Override]
    public function buildFromArray(array $settings)
    {
        $writer = $this->factory->createMemoryWriter();

        $this->setCommonSettings($writer, $settings);

        return $writer;
    }
}
