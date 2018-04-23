<?php

namespace Kronos\Log\Traits;

use Kronos\Log\Factory\Formatter;

trait ExceptionTraceBuilder
{
    /**
     * @var bool
     */
    protected $includeExceptionArgs = false;

    /**
     * @var int
     */
    protected $showExceptionTopLines = 0;

    /**
     * @var int
     */
    protected $showExceptionBottomLines = 0;

    /**
     * @var bool
     */
    protected $exceptionSettingChanged = false;

    /**
     * @var bool
     */
    protected $showPreviousException = true;

    /**
     * @var int
     */
    protected $showPreviousExceptionTopLines = 0;

    /**
     * @var int
     */
    protected $showPreviousExceptionBottomLines = 0;

    /**
     * @var bool
     */
    protected $previousExceptionSettingChanged = false;



    /**
     * @param bool $includeExceptionArgs
     */
    public function setIncludeExceptionArgs($includeExceptionArgs = true)
    {
        $this->includeExceptionArgs = $includeExceptionArgs;
    }

    /**
     * @param int $count
     */
    public function setShowExceptionTopLines($count = 4)
    {
        $this->showExceptionTopLines = $count;
    }

    /**
     * @param int $count
     */
    public function setShowExceptionBottomLines($count = 1)
    {
        $this->showExceptionBottomLines = $count;
    }

    /**
     * @param bool $show
     */
    public function setShowPreviousException($show = true)
    {
        $this->showPreviousException = $show;
    }

    /**
     * @param int $count
     */
    public function setShowPreviousExceptionTopLines($count = 4)
    {
        $this->showPreviousExceptionTopLines = $count;
    }

    /**
     * @param int $count
     */
    public function setShowPreviousExceptionBottomLines($count = 4)
    {
        $this->showPreviousExceptionBottomLines = $count;
    }

    /**
     * @param Formatter $formatterFactory
     * @return \Kronos\Log\Formatter\Exception\TraceBuilder
     */
    private function getExceptionTrace(Formatter $formatterFactory, $exception)
    {
        $builder = $formatterFactory->createExceptionTraceBuilder();
        $builder->getTraceAsString($exception);
    }

    /**
     * @param Formatter $formatterFactory
     * @return \Kronos\Log\Formatter\Exception\TraceBuilder
     */
    private function getPreviousTraceBuilder(Formatter $formatterFactory)
    {
        return $formatterFactory->createExceptionTraceBuilder();
    }
}