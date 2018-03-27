<?php

namespace Kronos\Log\Writer;

class Memory extends \Kronos\Log\AbstractWriter
{

    use \Kronos\Log\Traits\PrependLogLevel;

    /**
     * Contains all logged messages.
     * @var array
     */
    private $_logs = [];

    /**
     * Memory constructor.
     */
    public function __construct()
    {
        $this->setPrependLogLevel();
    }

    /**
     * Logs a message to the $_content array.
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        $interpolated_message = $this->interpolate($message, $context);
        $this->_logs[] = $this->prependLogLevel($level, $interpolated_message);
    }

    /**
     * Returns all logged messages.
     * @return array
     */
    public function getLogs()
    {
        return $this->_logs;
    }
}
