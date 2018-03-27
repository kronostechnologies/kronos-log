<?php

namespace Kronos\Log\Writer;

use Kronos\Log\Adaptor\FileFactory;
use Kronos\Log\ContextStringifier;
use Kronos\Log\Logger;
use Psr\Log\LogLevel;
use Kronos\Log\Exception\ExceptionTraceBuilder;
use Exception;

class File extends \Kronos\Log\AbstractWriter
{

    use \Kronos\Log\Traits\PrependDateTime;
    use \Kronos\Log\Traits\PrependLogLevel;

    const EXCEPTION_TITLE_LINE = "Exception: '{message}' in '{file}' at line {line}";
    const PREVIOUS_EXCEPTION_TITLE_LINE = "Previous exception: '{message}' in '{file}' at line {line}";
    const CONTEXT_TITLE_LINE = 'Context:';

    /**
     * @var \Kronos\Log\Adaptor\File
     */
    private $file_adaptor;

    /**
     * @var ContextStringifier
     */
    private $context_stringifier = null;

    /**
     * @var ExceptionTraceBuilder
     */
    private $trace_builder;

    /**
     * @var FileFactory
     */
    private $factory;

    /**
     * File constructor.
     * @param $filename
     * @param FileFactory $factory
     * @param ExceptionTraceBuilder|null $trace_builder
     */
    public function __construct($filename, FileFactory $factory = null, ExceptionTraceBuilder $trace_builder = null)
    {
        $this->factory = is_null($factory) ? new FileFactory() : $factory;
        $this->file_adaptor = $this->factory->createFileAdaptor($filename);
        $this->trace_builder = is_null($trace_builder) ? new ExceptionTraceBuilder() : $trace_builder;
    }

    /**
     * @param ContextStringifier $context_stringifier
     */
    public function setContextStringifier($context_stringifier)
    {
        $this->context_stringifier = $context_stringifier;
        $this->context_stringifier->excludeKey(Logger::EXCEPTION_CONTEXT);
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        $this->writeMessage($level, $message, $context);
        $this->writeExceptionIfGiven($message, $level, $context);
        $this->writeContextIfStringifierGiven($context);
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     */
    private function writeMessage($level, $message, array $context = [])
    {
        $interpolated_message = $this->interpolate($message, $context);
        $message_with_loglevel = $this->prependLogLevel($level, $interpolated_message);
        $message_with_datetime = $this->prependDateTime($message_with_loglevel);
        $this->file_adaptor->write($message_with_datetime);
    }

    /**
     * @param array $context
     */
    private function writeContextIfStringifierGiven(array $context = [])
    {
        if ($this->context_stringifier && !empty($context)) {
            $this->file_adaptor->write(self::CONTEXT_TITLE_LINE);
            $this->file_adaptor->write($this->context_stringifier->stringify($context));
        }
    }

    /**
     * @param $message
     * @param $level
     * @param array $context
     */
    private function writeExceptionIfGiven($message, $level, array $context)
    {
        if (isset($context[Logger::EXCEPTION_CONTEXT]) && $context[Logger::EXCEPTION_CONTEXT] instanceof Exception) {
            /** @var Exception $exception */
            $exception = $context[Logger::EXCEPTION_CONTEXT];
            $this->writeException($message, $level, $exception);
        }
    }

    /**
     * @param string $level
     * @param Exception $exception
     * @param int $depth
     */
    private function writeException($message, $level, Exception $exception, $depth = 0)
    {
        if ($message != $exception->getMessage()) {
            $this->writeExceptionTitle($exception, $depth);
        }

        if (!$this->isLevelLower(LogLevel::ERROR, $level)) {
            $ex_trace = $this->trace_builder->getTraceAsString($exception, $this->include_exception_args);
            $this->file_adaptor->write($ex_trace);
        }

        $previous = $exception->getPrevious();
        if ($previous instanceof Exception) {
            $this->writeException($message, $level, $previous, $depth + 1);
        }
    }

    /**
     * @param Exception $exception
     * @param $depth
     */
    private function writeExceptionTitle(Exception $exception, $depth)
    {
        $title = ($depth === 0 ? self::EXCEPTION_TITLE_LINE : self::PREVIOUS_EXCEPTION_TITLE_LINE);

        $title = strtr($title, [
            '{message}' => $exception->getMessage(),
            '{file}' => $exception->getFile(),
            '{line}' => $exception->getLine()
        ]);

        $this->file_adaptor->write($title);
    }
}