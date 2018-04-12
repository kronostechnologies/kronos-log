<?php

namespace Kronos\Log\Formatter\Exception;

/**
 * Class ExceptionTraceBuilder
 * @package Kronos\Log\Exception
 */
class TraceBuilder
{

    /**
     * @var \Kronos\Log\Formatter\Exception\LineBuilder
     */
    private $line_builder;

    /**
     * ExceptionTraceBuilder constructor.
     * @param LineBuilder|null $line_builder
     */
    public function __construct(LineBuilder $line_builder = null)
    {
        $this->line_builder = is_null($line_builder) ? new LineBuilder() : $line_builder;
    }

    /**
     * Builds the exception trace as a number of string lines separated by carriage return
     *
     * @param $exception
     * @param bool $include_args
     * @return string
     */
    public function getTraceAsString($exception, $include_args = false)
    {
        $ex_trace = "";
        $ex_elements = $exception->getTrace();

        if (!empty($ex_elements)) {
            foreach ($ex_elements as $stack_line_nb => $ex_element) {

                if (isset($stack_line_nb)) {
                    $this->line_builder->setLineNb($stack_line_nb);
                }

                if (isset($ex_element['line'])) {
                    $this->line_builder->setLine($ex_element['line']);
                }

                if (isset($ex_element['file'])) {
                    $this->line_builder->setFile($ex_element['file']);
                }

                if (isset($ex_element['class'])) {
                    $this->line_builder->setClass($ex_element['class']);
                }

                if (isset($ex_element['function'])) {
                    $this->line_builder->setFunction($ex_element['function']);
                }

                if (isset($ex_element['type'])) {
                    $this->line_builder->setType($ex_element['type']);
                }

                if ($include_args && isset($ex_element['args'])) {
                    $this->line_builder->setArgs($ex_element['args']);
                }

                $ex_trace .= $this->line_builder->buildExceptionString() . "\n";

                $this->line_builder->clearLine();
            }
        }
        return $ex_trace;
    }
}