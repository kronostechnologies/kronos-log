<?php


namespace Kronos\Log\Writer;


use Fluent\Logger\FluentLogger;
use \Kronos\Log\Factory\Fluentd\FluentBitJsonPacker;
use Override;

class Fluentbit extends Fluentd
{
    /**
     * @return FluentLogger
     */
    #[Override]
    protected function initializeLogger()
    {
        if ($this->logger === null) {
            $this->logger = $this->factory->createFluentLogger($this->hostname, $this->port, [], new FluentBitJsonPacker());
        }

        return $this->logger;
    }
}
