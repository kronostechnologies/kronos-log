<?php


namespace Kronos\Log\Factory;


use Fluent\Logger\FluentLogger;

class Fluentd
{
    /**
     * @param string $hostname
     * @param int $port
     * @return FluentLogger
     */
    public function createFluentLogger($hostname, $port)
    {
        return new FluentLogger($hostname, $port);
    }
}