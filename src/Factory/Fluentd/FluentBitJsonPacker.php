<?php


namespace Kronos\Log\Factory\Fluentd;

use Fluent\Logger\FluentLogger;
use Fluent\Logger\Entity;
use Fluent\Logger\PackerInterface;
use Override;

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
    #[Override]
    public function pack(Entity $entity): string
    {
        $data = $entity->getData();
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
