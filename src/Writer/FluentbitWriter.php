<?php


namespace Kronos\Log\Writer;


use Fluent\Logger\FluentLogger;
use Kronos\Log\Factory\Fluentd\FluentBitJsonPacker;
use Override;

class FluentbitWriter extends FluentdWriter
{
    #[Override]
    protected function initializeLogger(): FluentLogger
    {
        if ($this->logger === null) {
            $this->logger = $this->factory->createFluentLogger($this->hostname, $this->port, [], new FluentBitJsonPacker());
        }

        return $this->logger;
    }
}
