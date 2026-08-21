<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter;
use Kronos\Log\Adaptor\FileAdaptor;
use Kronos\Log\Adaptor\FileAdaptorFactory;
use Kronos\Log\Formatter\ContextStringifier;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Logger;
use Kronos\Log\Traits\PrependDateTime;
use Kronos\Log\Traits\PrependLogLevel;
use Override;
use Psr\Log\LogLevel;
use Stringable;
use Throwable;

class FileWriter extends AbstractWriter
{
    use PrependDateTime;
    use PrependLogLevel;

    const string EXCEPTION_TITLE_LINE = "Exception: '{message}' in '{file}' at line {line}";
    const string PREVIOUS_EXCEPTION_TITLE_LINE = "Previous exception: '{message}' in '{file}' at line {line}";
    const string CONTEXT_TITLE_LINE = 'Context:';

    private FileAdaptor $fileAdaptor;
    private ?ContextStringifier $contextStringifier = null;
    private ?TraceBuilder $exceptionTraceBuilder;
    private ?TraceBuilder $previousExceptionTraceBuilder;
    private FileAdaptorFactory $factory;

    public function __construct(
        ?string $filename,
        ?FileAdaptorFactory $factory = null,
        ?TraceBuilder $exceptionTraceBuilder = null,
        ?TraceBuilder $previousExceptionTraceBuilder = null
    ) {
        $this->factory = $factory ?? new FileAdaptorFactory();
        $this->fileAdaptor = $this->factory->createFileAdaptor($filename);
        $this->exceptionTraceBuilder = $exceptionTraceBuilder;
        $this->previousExceptionTraceBuilder = $previousExceptionTraceBuilder;
    }

    public function setContextStringifier(ContextStringifier $contextStringifier): void
    {
        $this->contextStringifier = $contextStringifier;
        $this->contextStringifier->excludeKey(Logger::EXCEPTION_CONTEXT);
    }

    #[Override]
    public function log(string $level, string | Stringable $message, array $context = []): void
    {
        $this->writeMessage($level, $message, $context);
        $this->writeExceptionIfGiven($message, $level, $context);
        $this->writeContextIfStringifierGiven($context);
    }

    private function writeMessage(string $level, string | Stringable $message, array $context = []): void
    {
        $interpolated_message = $this->interpolate($message, $context);
        $message_with_loglevel = $this->prependLogLevel($level, $interpolated_message);
        $message_with_datetime = $this->prependDateTime($message_with_loglevel);
        $this->fileAdaptor->write($message_with_datetime);
    }

    private function writeContextIfStringifierGiven(array $context = []): void
    {
        if ($this->contextStringifier && !empty($context)) {
            $this->fileAdaptor->write(self::CONTEXT_TITLE_LINE);
            $this->fileAdaptor->write($this->contextStringifier->stringify($context));
        }
    }

    private function writeExceptionIfGiven(string | Stringable $message, string $level, array $context): void
    {
        $exception = $context[Logger::EXCEPTION_CONTEXT] ?? null;
        if ($exception instanceof Throwable) {
            $this->writeException($message, $level, $exception);
        }
    }

    private function writeException(
        string | Stringable $message,
        string $level,
        Throwable $exception,
        int $depth = 0
    ): void {
        if ($message != $exception->getMessage()) {
            $this->writeExceptionTitle($exception, $depth);
        }

        if (!$this->isLevelLower(LogLevel::ERROR, $level)) {
            if ($depth > 0) {
                if ($this->previousExceptionTraceBuilder) {
                    $ex_trace = $this->previousExceptionTraceBuilder->getTraceAsString($exception);
                    $this->fileAdaptor->write($ex_trace);
                }
            } elseif ($this->exceptionTraceBuilder) {
                $ex_trace = $this->exceptionTraceBuilder->getTraceAsString($exception);
                $this->fileAdaptor->write($ex_trace);
            }
        }

        $this->fileAdaptor->write('');

        $previous = $exception->getPrevious();
        if ($previous instanceof Throwable) {
            $this->writeException($message, $level, $previous, $depth + 1);
        }
    }


    private function writeExceptionTitle(Throwable $exception, int $depth): void
    {
        $title = ($depth === 0 ? self::EXCEPTION_TITLE_LINE : self::PREVIOUS_EXCEPTION_TITLE_LINE);

        $title = strtr($title, [
            '{message}' => $exception->getMessage(),
            '{file}' => $exception->getFile(),
            '{line}' => $exception->getLine()
        ]);

        $this->fileAdaptor->write($title);
    }
}
