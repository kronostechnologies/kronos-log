<?php

namespace Kronos\Log\Factory;

class LoggerFactory
{

    /**
     * @return \Kronos\Log\Logger
     */
    public function createLogger()
    {
        return new \Kronos\Log\Logger();
    }
}
