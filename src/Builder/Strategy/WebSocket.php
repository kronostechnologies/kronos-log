<?php


namespace Kronos\Log\Builder\Strategy;


use Kronos\Log\Factory\Writer as WriterFactory;

class WebSocket extends AbstractWriter
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
     * @return \Kronos\Log\Writer\WebSocket
     */
    public function buildFromArray(array $settings): \Kronos\Log\Writer\WebSocket
    {
        $writer = $this->factory->createWebsocketWriter($settings['websocket']);

        $this->setCommonSettings($writer, $settings);

        return $writer;
    }
}
