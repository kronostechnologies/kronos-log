<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter;
use WebSocket\Client;
use Kronos\Log\Traits\PrependContext;

class WebSocket extends AbstractWriter
{
    use PrependContext;

    private const PREFIX = "ws://";

    /**
     * @var Client
     */
    private $client;

    public function __construct($websocket)
    {
        $this->client = new Client(self::PREFIX . $websocket);
    }

    public function log($level, $message, array $context = [])
    {
        $interpolatedMessage = $this->interpolate($message, $context);
        $prependedMessage = $this->prependContext($interpolatedMessage, $context);

        $this->client->text($prependedMessage);
    }
}
