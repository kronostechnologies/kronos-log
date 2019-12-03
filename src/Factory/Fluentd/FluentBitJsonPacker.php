<?php


namespace Kronos\Log\Factory\Fluentd;

use Fluent\Logger\FluentLogger;
use Fluent\Logger\Entity;
use Fluent\Logger\PackerInterface;

class FluentBitJsonPacker implements PackerInterface
{

    public function __construct()
    {
    }

    /**
     * pack entity as a json string for fluentbit, removing timestamp and tag key (no time)
     *
     * @param Entity $entity
     * @return string
     */
    public function pack(Entity $entity)
    {
        $data = $entity->getData();
        return json_encode($data);
    }
}
