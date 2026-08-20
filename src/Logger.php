<?php

namespace Kronos\Log;

use Override;
use Throwable;

class Logger extends \Psr\Log\AbstractLogger implements LoggerInterface
{

    const string EXCEPTION_CONTEXT = 'exception';
    const string WRITER_PATH = "\Kronos\Log\Writer\\";

    private array $context = [];

    /**
     * @var WriterInterface[]
     */
    private array $writers = [];

    /**
     * @param WriterInterface $writer
     */
    public function addWriter(WriterInterface $writer): void
    {
        $this->writers[] = $writer;
    }

    /**
     * @return WriterInterface[]
     */
    public function getWriters(): array
    {
        return $this->writers;
    }

    #[Override]
    public function addContext(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
    }

    #[Override]
    public function addContextArray(array $context): void
    {
        $this->context = array_merge($this->context, $context);
    }

    public function setWriterCanLog(string $writer_name, bool $can_log = true): void
    {
        /** @var class-string $writerClassName */
        $writerClassName = self::WRITER_PATH . ucfirst($writer_name);
        foreach ($this->writers as $writer) {
            if (is_a($writer, $writerClassName, true)) {
                $writer->setCanLog($can_log);
            }
        }
    }

    #[Override]
    public function log($level, string|\Stringable $message, array $context = array()): void
    {
        foreach ($this->writers as $writer) {
            if ($writer->canLogLevel($level)) {
                try {
                    $writer->log($level, $message, $context + $this->context);
                } catch (\Exception $exception) {
                    trigger_error($exception->getMessage(), E_USER_WARNING);
                }
            }
        }
    }

    /**
     * Log error with exception context
     */
    #[Override]
    public function exception(string|\Stringable $message, Throwable $exception, array $context = array()): void
    {
        $context[self::EXCEPTION_CONTEXT] = $exception;
        $this->error($message, $context);
    }

    /**
     * Log warning with exception context
     */
    #[Override]
    public function exceptionWarning(string|\Stringable $message, Throwable $exception, array $context = array()): void
    {
        $context[self::EXCEPTION_CONTEXT] = $exception;
        $this->warning($message, $context);
    }
}
