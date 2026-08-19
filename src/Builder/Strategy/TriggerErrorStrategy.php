<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Factory\WriterFactory AS WriterFactory;
use Kronos\Log\WriterInterface;
use Override;

class TriggerErrorStrategy extends AbstractWriterStrategy
{
    private WriterFactory $factory;

    public function __construct(?WriterFactory $factory = null)
    {
        $this->factory = is_null($factory) ? new WriterFactory() : $factory;
    }

    /**
     * @param array $settings
     * @return \Kronos\Log\Writer\TriggerErrorWriter|WriterInterface
     */
    #[Override]
    public function buildFromArray(array $settings)
    {
        $writer = $this->factory->createTriggerErrorWriter();

        $this->setCommonSettings($writer, $settings);

        return $writer;
    }

}
