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
     * pack entity as a json string for fluent bit with the ultimate tag name encoded as a key (no time)
     *
     * @param Entity $entity
     * @return string
     */
    public function pack(Entity $entity)
    {
        $data = $entity->getData();
        $data['_tag'] = $entity->getTag();
        return json_encode($data);
    }
}
