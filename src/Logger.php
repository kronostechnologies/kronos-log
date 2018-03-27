<?php

namespace Kronos\Log;

use Kronos\Log\Writer\Console;

class Logger extends \Psr\Log\AbstractLogger
{

    const EXCEPTION_CONTEXT = 'exception';
    const WRITER_PATH = "\Kronos\Log\Writer\\";

    private $context = [];

    /**
     * @var WriterInterface[]
     */
    private $writers = [];

    /**
     * @param WriterInterface $writer
     */
    public function addWriter(WriterInterface $writer)
    {
        $this->writers[] = $writer;
    }

    /**
     * @param $key String
     * @param $value Mixed
     */
    public function addContext($key, $value)
    {
        $this->context[$key] = $value;
    }

    public function addContextArray(array $context)
    {
        $this->context = array_merge($this->context, $context);
    }

    public function setWriterCanLog($writer_name, $can_log = true)
    {
        foreach ($this->writers as $writer) {
            if (is_a($writer, self::WRITER_PATH . ucfirst($writer_name), true)) {
                $writer->setCanLog($can_log);
            }
        }
    }

    public function log($level, $message, array $context = array())
    {
        foreach ($this->writers as $writer) {
            if ($writer->canLogLevel($level)) {
                $writer->log($level, $message, $context + $this->context);
            }
        }
    }
}