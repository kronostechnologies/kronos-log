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
     * @var LineAssemblerBuilder
     */
    private $lineAssemblerBuilder;

    /**
     * ExceptionTraceBuilder constructor.
     * @param LineAssemblerBuilder|null $lineAssemblerBuilder
     */
    public function __construct(LineAssemblerBuilder $lineAssemblerBuilder = null)
    {
        $this->lineAssemblerBuilder = is_null($lineAssemblerBuilder) ? new LineAssemblerBuilder() : $lineAssemblerBuilder;
    }

    /**
     * Builds the exception trace as a number of string lines separated by carriage return
     *
     * @param $exception
     * @return string
     */
    public function getTraceAsString(Throwable $exception): string
    {

        $lines = [];
        $traceStack = $exception->getTrace();
        $this->generateAcceptedLineRange(count($traceStack));

        $addedLineSkip = false;

        if (!empty($traceStack)) {
            foreach ($traceStack as $stackLineNumber => $stackItem) {

                if ($this->shouldBuildLine($stackLineNumber)) {
                    $lineAssembler = $this->lineAssemblerBuilder->buildAssembler();
                    $this->setupLineBuilder($lineAssembler, $stackLineNumber, $stackItem);
                    $lines[] = $lineAssembler->buildExceptionString();
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
     * @param LineAssembler $lineAssembler
     * @param $stackLineNumber
     * @param $traceElement
     */
    private function setupLineBuilder(LineAssembler $lineAssembler, $stackLineNumber, $traceElement)
    {
        if (isset($stackLineNumber)) {
            $lineAssembler->setLineNb($stackLineNumber);
        }

        if (isset($traceElement['line'])) {
            $lineAssembler->setLine($traceElement['line']);
        }

        if (isset($traceElement['file'])) {
            $lineAssembler->setFile($traceElement['file']);
        }

        if (isset($traceElement['class'])) {
            $lineAssembler->setClass($traceElement['class']);
        }

        if (isset($traceElement['function'])) {
            $lineAssembler->setFunction($traceElement['function']);
        }

        if (isset($traceElement['type'])) {
            $lineAssembler->setType($traceElement['type']);
        }

        if ($this->includeArgs && isset($traceElement['args'])) {
            $lineAssembler->setArgs($traceElement['args']);
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
