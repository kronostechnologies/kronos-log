<?php


namespace Kronos\Log\Traits;


use Kronos\Log\Formatter\Exception\TraceBuilder;
use Throwable;

trait ExceptionTraceBuilderAwareTrait
{

    /**
     * @return TraceBuilder|null
     */
    abstract function getExceptionTraceBuilder();

    /**
     * @return TraceBuilder|null
     */
    abstract function getPreviousExceptionTraceBuilder();

    /**
     * @param array $context
     * @return mixed
     */
    protected function replaceException(array $context)
    {
        if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
            $context['exception'] = $this->getExceptionContext($context['exception']);
        }

        return $context;
    }


    protected function getExceptionContext(Throwable $exception): array
    {
        $context = [
            'message' => $exception->getMessage()
        ];

        if ($exceptionTraceBuilder = $this->getExceptionTraceBuilder()) {
            $context['stacktrace'] = $exceptionTraceBuilder->getTraceAsString($exception);
        }

        $previous = $exception->getPrevious();
        if ($previous instanceof Throwable) {
            $context['previous'] = $this->getPreviousExceptionContext($previous);
        }

        return $context;
    }

    protected function getPreviousExceptionContext(Throwable $exception): array
    {
        $context = [
            'message' => $exception->getMessage()
        ];

        if ($previousExceptionTraceBuilder = $this->getPreviousExceptionTraceBuilder()) {
            $context['stacktrace'] = $previousExceptionTraceBuilder->getTraceAsString($exception);
        }

        $previous = $exception->getPrevious();
        if ($previous instanceof Throwable) {
            $context['previous'] = $this->getPreviousExceptionContext($previous);
        }

        return $context;
    }

}
