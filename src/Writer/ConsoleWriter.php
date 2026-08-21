<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter;
use Kronos\Log\Adaptor\FileAdaptorFactory;
use Kronos\Log\Adaptor\TTYAdaptor;
use Kronos\Log\Enumeration\AnsiBackgroundColor;
use Kronos\Log\Enumeration\AnsiTextColor;
use Kronos\Log\Traits\PrependDateTime;
use Kronos\Log\Traits\PrependLogLevel;
use Kronos\Log\Logger;
use Override;
use Psr\Log\LogLevel;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Stringable;
use Throwable;

class ConsoleWriter extends AbstractWriter
{
    use PrependLogLevel;
    use PrependDateTime;

    const string STDOUT = 'php://stdout';
    const string STDERR = 'php://stderr';
    const string EXCEPTION_TITLE_LINE = "Exception: '{message}' in '{file}' at line {line}";
    const string PREVIOUS_EXCEPTION_TITLE_LINE = "Previous exception: '{message}' in '{file}' at line {line}";

    private TTYAdaptor $stdout;
    private TTYAdaptor $stderr;
    private ?TraceBuilder $exceptionTraceBuilder;
    private ?TraceBuilder $previousExceptionTraceBuilder;

    public function __construct(
        ?TraceBuilder $exceptionTraceBuilder = null,
        ?TraceBuilder $previousExceptionTraceBuilder = null,
        ?FileAdaptorFactory $factory = null,
    ) {
        $factory = $factory ?: new FileAdaptorFactory();
        $this->stdout = $factory->createTTYAdaptor(self::STDOUT);
        $this->stderr = $factory->createTTYAdaptor(self::STDERR);

        $this->exceptionTraceBuilder = $exceptionTraceBuilder;
        $this->previousExceptionTraceBuilder = $previousExceptionTraceBuilder;
    }

    #[Override]
    public function log(string $level, string | Stringable $message, array $context = []): void
    {
        $interpolated_message = $this->interpolate($message, $context);
        $message_with_loglevel = $this->prependLogLevel($level, $interpolated_message);
        $message_with_datetime = $this->prependDateTime($message_with_loglevel);

        if ($this->isLevelLower(LogLevel::ERROR, $level)) {
            $this->stdout->write($message_with_datetime, $this->getLevelTextColor($level));
        } else {
            $this->stderr->write($message_with_datetime, AnsiTextColor::WHITE, AnsiBackgroundColor::RED);
        }

        $this->writeExceptionIfGiven($message, $level, $context);
    }

    public function setForceAnsiColorSupport(bool $force = true): void
    {
        $this->stdout->setForceAnsiColorSupport($force);
        $this->stderr->setForceAnsiColorSupport($force);
    }

    public function setForceNoAnsiColorSupport(bool $force = true): void
    {
        $this->stdout->setForceNoAnsiColorSupport($force);
        $this->stderr->setForceNoAnsiColorSupport($force);
    }

    private function getLevelTextColor($level): ?string
    {
        return $level == LogLevel::WARNING ? AnsiTextColor::YELLOW : null;
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
                    $this->stderr->write($ex_trace);
                }
            } elseif ($this->exceptionTraceBuilder) {
                $ex_trace = $this->exceptionTraceBuilder->getTraceAsString($exception);
                $this->stderr->write($ex_trace);
            }
        }

        $this->stderr->write('');

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

        $this->stderr->write($title);
    }
}
