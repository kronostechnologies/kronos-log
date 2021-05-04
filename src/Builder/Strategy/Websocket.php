<?php


namespace Kronos\Log\Builder\Strategy;


use Kronos\Log\Factory\Writer as WriterFactory;

class Websocket extends AbstractWriter
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
     * @return \Kronos\Log\Writer\Websocket
     */
    public function buildFromArray(array $settings): \Kronos\Log\Writer\Websocket
    {
        $writer = $this->factory->createWebsocketWriter($settings['websocket']);

        $this->setCommonSettings($writer, $settings);

        return $writer;
    }
}
