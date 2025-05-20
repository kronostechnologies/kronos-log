<?php

namespace Kronos\Log\Factory;

use Fluent\Logger\FluentLogger;
use Fluent\Logger\PackerInterface;

class Fluentd
{
    /**
     * @param string $hostname
     * @param int $port
     * @param array $options
     * @param ?PackerInterface $packer
     * @return FluentLogger
     */
    public function createFluentLogger($hostname, $port, $options = [], ?PackerInterface $packer = null)
    {
        return new FluentLogger($hostname, $port, $options, $packer);
    }
}
