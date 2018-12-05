<?php


namespace Kronos\Log\Traits;


use Kronos\Log\Formatter\Exception\TraceBuilder;

trait ExceptionTraceBuilderAwareTrait
{

    /**
     * @return TraceBuilder
     */
    abstract function getExceptionTraceBuilder();

    /**
     * @return TraceBuilder
     */
    abstract function getPreviousExceptionTraceBuilder();

    /**
     * @param array $context
     * @return mixed
     */
    protected function replaceException(array $context)
    {
        if (isset($context['exception']) && $context['exception'] instanceof \Exception) {
            $context['exception'] = $this->getExceptionContext($context['exception']);
        }

        return $context;
    }

    /**
     * @param \Exception $exception
     * @return array
     */
    protected function getExceptionContext(\Exception $exception){
        $context = [
            'message' => $exception->getMessage()
        ];

        if($this->getExceptionTraceBuilder()){
            $context['stacktrace'] = $this->getExceptionTraceBuilder()->getTraceAsString($exception);
        }

        $previous = $exception->getPrevious();
        if ($previous instanceof \Exception) {
            $context['previous'] = $this->getPreviousExceptionContext($previous);
        }

        return $context;
    }

    /**
     * @param \Exception $exception
     * @return array
     */
    protected function getPreviousExceptionContext(\Exception $exception)
    {
        $context = [
            'message' => $exception->getMessage()
        ];

        if($this->getPreviousExceptionTraceBuilder()){
            $context['stacktrace'] = $this->getPreviousExceptionTraceBuilder()->getTraceAsString($exception);
        }

        $previous = $exception->getPrevious();
        if ($previous instanceof \Exception) {
            $context['previous'] = $this->getPreviousExceptionContext($previous);
        }

        return $context;
    }

}
