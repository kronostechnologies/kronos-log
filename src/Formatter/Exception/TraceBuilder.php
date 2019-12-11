<?php

namespace Kronos\Log\Formatter\Exception;

use Throwable;

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
     * @var LineAssembler
     */
    private $lineAssembler;

    /**
     * ExceptionTraceBuilder constructor.
     * @param LineAssembler|null $lineAssembler
     */
    public function __construct(LineAssembler $lineAssembler = null)
    {
        $this->lineAssembler = is_null($lineAssembler) ? new LineAssembler() : $lineAssembler;
    }

    /**
     * Builds the exception trace as a number of string lines separated by carriage return
     *
     * @param $exception
     * @return string
     */
    public function getTraceAsString(Throwable $exception)
    {
        $lines = [];
        $traceStack = $exception->getTrace();
        $this->generateAcceptedLineRange(count($traceStack));

        $addedLineSkip = false;

        if (!empty($traceStack)) {
            foreach ($traceStack as $stackLineNumber => $stackItem) {

                if ($this->shouldBuildLine($stackLineNumber)) {
                    $this->setupLineBuilder($stackLineNumber, $stackItem);

                    $lines[] = $this->lineAssembler->buildExceptionString();

                    $this->lineAssembler->clearLine();
                } elseif (!$addedLineSkip) {
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
    public function includeArgs($includeArgs = true)
    {
        $this->includeArgs = $includeArgs;
    }

    /**
     * @param $stackLineNumber
     * @param $traceElement
     */
    private function setupLineBuilder($stackLineNumber, $traceElement)
    {
        if (isset($stackLineNumber)) {
            $this->lineAssembler->setLineNb($stackLineNumber);
        }

        if (isset($traceElement['line'])) {
            $this->lineAssembler->setLine($traceElement['line']);
        }

        if (isset($traceElement['file'])) {
            $this->lineAssembler->setFile($traceElement['file']);
        }

        if (isset($traceElement['class'])) {
            $this->lineAssembler->setClass($traceElement['class']);
        }

        if (isset($traceElement['function'])) {
            $this->lineAssembler->setFunction($traceElement['function']);
        }

        if (isset($traceElement['type'])) {
            $this->lineAssembler->setType($traceElement['type']);
        }

        if ($this->includeArgs && isset($traceElement['args'])) {
            $this->lineAssembler->setArgs($traceElement['args']);
        }
    }

    /**
     * @param $stackLineNumber
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
