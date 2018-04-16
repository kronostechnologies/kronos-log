<?php

namespace Kronos\Log\Traits;

trait ExceptionTraceSettings
{
    /**
     * @var bool
     */
    protected $includeExceptionArgs = false;

    protected $showExceptionTopLines = 0;

    protected $showExceptionBottomLines = 0;

    protected $showPreviousException = true;

    protected $showPreviousExceptionTopLines = 0;

    protected $showPreviousExceptionBottomLines = 0;

    /**
     * @param bool $includeExceptionArgs
     */
    public function setIncludeExceptionArgs($includeExceptionArgs)
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

}