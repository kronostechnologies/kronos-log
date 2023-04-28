<?php

namespace Kronos\Log;

use Throwable;

class Logger extends \Psr\Log\AbstractLogger implements LoggerInterface
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
    public function addWriter(WriterInterface $writer): void
    {
        $this->writers[] = $writer;
    }

    public function addContext(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
    }

    public function addContextArray(array $context): void
    {
        $this->context = array_merge($this->context, $context);
    }

    public function setWriterCanLog($writer_name, $can_log = true): void
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

    /**
     * Log Error with exception context
     */
    public function exception(string $message, Throwable $exception, array $context = array()): void
    {
        $context[self::EXCEPTION_CONTEXT] = $exception;
        $this->error($message, $context);
    }
}
