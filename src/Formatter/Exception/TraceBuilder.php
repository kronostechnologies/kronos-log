<?php

namespace Kronos\Log\Formatter\Exception;

/**
 * Class ExceptionTraceBuilder
 * @package Kronos\Log\Exception
 */
class TraceBuilder
{
    const LINE_SKIP = '...';

    /**
     * @var bool
     */
    private $showTopLines = false;

    /**
     * @var int
     */
    private $topLines = 0;

    /**
     * @var bool
     */
    private $showBottomLines = false;

    /**
     * @var integer
     */
    private $bottomLines = 0;

    /**
     * @var array
     */
    private $acceptedRange;

    /**
     * @var bool
     */
    private $includeArgs = false;

    /**
     * @var \Kronos\Log\Formatter\Exception\LineBuilder
     */
    private $lineBuilder;

    /**
     * ExceptionTraceBuilder constructor.
     * @param LineBuilder|null $lineBuilder
     */
    public function __construct(LineBuilder $lineBuilder = null)
    {
        $this->lineBuilder = is_null($lineBuilder) ? new LineBuilder() : $lineBuilder;
    }

    /**
     * Builds the exception trace as a number of string lines separated by carriage return
     *
     * @param $exception
     * @param bool $includeArgs
     * @return string
     */
    public function getTraceAsString($exception) // Once we support PHP 7 => $exception should be a \Throwable
    {
        $lines = [];
        $traceStack = $exception->getTrace();
        $this->generateAcceptedLineRange(count($traceStack));

        $addedLineSkip = false;

        if (!empty($traceStack)) {
            foreach ($traceStack as $stackLineNumber => $stackItem) {

                if($this->shouldBuildLine($stackLineNumber)) {
                    $this->setupLineBuilder($stackLineNumber, $stackItem);

                    $lines[] = $this->lineBuilder->buildExceptionString();

                    $this->lineBuilder->clearLine();
                }
                else if(!$addedLineSkip) {
                    $lines[] = self::LINE_SKIP;

                    $addedLineSkip = true;
                }
            }
        }
        return implode(PHP_EOL, $lines);
    }

    /**
     * @param int $lines
     */
    public function showTopLines($lines)
    {
        $this->showTopLines = true;
        $this->topLines = $lines;
    }

    /**
     * @param int $lines
     */
    public function showBottomLines($lines)
    {
        $this->showBottomLines = true;
        $this->bottomLines = $lines;
    }

    /**
     * @param $includeArgs
     */
    public function includeArgs($includeArgs = true) {
        $this->includeArgs = $includeArgs;
    }

    /**
     * @param $stackLineNumber
     * @param $traceElement
     */
    private function setupLineBuilder($stackLineNumber, $traceElement)
    {
        if (isset($stackLineNumber)) {
            $this->lineBuilder->setLineNb($stackLineNumber);
        }

        if (isset($traceElement['line'])) {
            $this->lineBuilder->setLine($traceElement['line']);
        }

        if (isset($traceElement['file'])) {
            $this->lineBuilder->setFile($traceElement['file']);
        }

        if (isset($traceElement['class'])) {
            $this->lineBuilder->setClass($traceElement['class']);
        }

        if (isset($traceElement['function'])) {
            $this->lineBuilder->setFunction($traceElement['function']);
        }

        if (isset($traceElement['type'])) {
            $this->lineBuilder->setType($traceElement['type']);
        }

        if ($this->includeArgs && isset($traceElement['args'])) {
            $this->lineBuilder->setArgs($traceElement['args']);
        }
    }

    /**
     * @param $stackLineNumber
     * @param $stackHeight
     * @return bool
     */
    private function shouldBuildLine($stackLineNumber)
    {
        return (!$this->showTopLines && !$this->showBottomLines) || in_array($stackLineNumber, $this->acceptedRange);
    }

    /**
     * @param $stackHeight
     */
    private function generateAcceptedLineRange($stackHeight)
    {
        $acceptedRange = [];

        if ($this->showTopLines) {
            $acceptedRange = array_merge($acceptedRange, range(0, $this->topLines - 1));
        }

        if ($this->showBottomLines) {
            $acceptedRange = array_merge($acceptedRange, range($stackHeight - $this->bottomLines, $stackHeight - 1));
        }

        $this->acceptedRange = $acceptedRange;
    }

}