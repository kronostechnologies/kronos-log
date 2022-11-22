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
        /** @var class-string $writerClassName */
        $writerClassName = self::WRITER_PATH . ucfirst($writer_name);
        foreach ($this->writers as $writer) {
            if (is_a($writer, $writerClassName, true)) {
                $writer->setCanLog($can_log);
            }
        }
    }

    public function log($level, $message, array $context = array()): void
    {
        foreach ($this->writers as $writer) {
            if ($writer->canLogLevel($level)) {
                try {
                    $writer->log($level, $message, $context + $this->context);
                } catch (\Exception $exception) {
                    trigger_error($exception->getMessage(), E_USER_ERROR);
                }
            }
        }
    }
}
