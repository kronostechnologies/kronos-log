<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Factory\Writer AS WriterFactory;
use Kronos\Log\WriterInterface;

class TriggerError extends AbstractWriter
{
    /**
     * @var WriterFactory
     */
    private $factory;

    public function __construct(WriterFactory $factory = null)
    {
        $this->factory = is_null($factory) ? new WriterFactory() : $factory;
    }

    /**
     * @param array $settings
     * @return \Kronos\Log\Writer\TriggerError|WriterInterface
     */
    public function buildFromArray(array $settings)
    {
        $writer = $this->factory->createTriggerErrorWriter();

        $this->setCommonSettings($writer, $settings);

        return $writer;
    }

}