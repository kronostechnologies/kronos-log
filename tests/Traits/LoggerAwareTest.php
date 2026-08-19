<?php

namespace Kronos\Tests\Log\Traits;

use Kronos\Log\Logger;
use Kronos\Log\Traits\LoggerAware;
use PHPUnit\Framework\Attributes\Test;

class LoggerAwareTest extends \PHPUnit\Framework\TestCase
{

    const A_MESSAGE = 'a message';
    const CONTEXT_KEY = 'key';
    const CONTEXT_VALUE = 'value';
    const EXCEPTION_MESSAGE = 'Some exception message';

    private $logger;

    private $trait;

    public function setUp(): void
    {
        $this->logger = $this->createMock(Logger::class);

        $this->trait = new TestableLoggerAware();
    }

    #[Test]
    public function traitWithLogger_logEmergency_ShouldCallEmergency()
    {
        $this->loggerExpectsMethodToBeCalledOnceWith('emergency', self::A_MESSAGE,
            [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
        $this->trait->setLogger($this->logger);

        $this->trait->callLogMethod('logEmergency', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithoutLogger_logEmergency_ShouldNotCallEmergency()
    {
        $this->loggerExpectsMethodNeverToBeCalled('emergency');

        $this->trait->callLogMethod('logEmergency', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithLogger_logAlert_ShouldCallAlert()
    {
        $this->loggerExpectsMethodToBeCalledOnceWith('alert', self::A_MESSAGE,
            [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
        $this->trait->setLogger($this->logger);

        $this->trait->callLogMethod('logAlert', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithoutLogger_logAlert_ShouldNotCallAlert()
    {
        $this->loggerExpectsMethodNeverToBeCalled('alert');

        $this->trait->callLogMethod('logAlert', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithLogger_logCritical_ShouldCallCritical()
    {
        $this->loggerExpectsMethodToBeCalledOnceWith('critical', self::A_MESSAGE,
            [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
        $this->trait->setLogger($this->logger);

        $this->trait->callLogMethod('logCritical', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithoutLogger_logCritical_ShouldNotCallCritical()
    {
        $this->loggerExpectsMethodNeverToBeCalled('critical');

        $this->trait->callLogMethod('logCritical', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithLogger_logError_ShouldCallError()
    {
        $this->loggerExpectsMethodToBeCalledOnceWith('error', self::A_MESSAGE,
            [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
        $this->trait->setLogger($this->logger);

        $this->trait->callLogMethod('logError', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithoutLogger_logError_ShouldNotCallError()
    {
        $this->loggerExpectsMethodNeverToBeCalled('error');

        $this->trait->callLogMethod('logError', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithLogger_logWarning_ShouldCallWarning()
    {
        $this->loggerExpectsMethodToBeCalledOnceWith('warning', self::A_MESSAGE,
            [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
        $this->trait->setLogger($this->logger);

        $this->trait->callLogMethod('logWarning', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithoutLogger_logWarning_ShouldNotCallWarning()
    {
        $this->loggerExpectsMethodNeverToBeCalled('warning');

        $this->trait->callLogMethod('logWarning', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithLogger_logNotice_ShouldCallNotice()
    {
        $this->loggerExpectsMethodToBeCalledOnceWith('notice', self::A_MESSAGE,
            [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
        $this->trait->setLogger($this->logger);

        $this->trait->callLogMethod('logNotice', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithoutLogger_logNotice_ShouldNotCallNotice()
    {
        $this->loggerExpectsMethodNeverToBeCalled('notice');

        $this->trait->callLogMethod('logNotice', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithLogger_logInfo_ShouldCallInfo()
    {
        $this->loggerExpectsMethodToBeCalledOnceWith('info', self::A_MESSAGE,
            [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
        $this->trait->setLogger($this->logger);

        $this->trait->callLogMethod('logInfo', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithoutLogger_logInfo_ShouldNotCallInfo()
    {
        $this->loggerExpectsMethodNeverToBeCalled('info');

        $this->trait->callLogMethod('logInfo', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithLogger_logDebug_ShouldCallDebug()
    {
        $this->loggerExpectsMethodToBeCalledOnceWith('debug', self::A_MESSAGE,
            [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
        $this->trait->setLogger($this->logger);

        $this->trait->callLogMethod('logDebug', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithoutLogger_logDebug_ShouldNotCallDebug()
    {
        $this->loggerExpectsMethodNeverToBeCalled('debug');

        $this->trait->callLogMethod('logDebug', self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function traitWithLogger_logException_ShouldCallError()
    {
        $exception = new \Exception(self::EXCEPTION_MESSAGE);
        $this->trait->setLogger($this->logger);
        $this->loggerExpectsMethodToBeCalledOnceWith('error', self::A_MESSAGE, [
            self::CONTEXT_KEY => self::CONTEXT_VALUE,
            Logger::EXCEPTION_CONTEXT => $exception
        ]);

        $this->trait->callLogException(self::A_MESSAGE, $exception, [
            self::CONTEXT_KEY => self::CONTEXT_VALUE
        ]);
    }

    private function loggerExpectsMethodToBeCalledOnceWith($method, $message, $context)
    {
        $this->logger
            ->expects($this->once())
            ->method($method)
            ->with($message, $context);
    }

    private function loggerExpectsMethodNeverToBeCalled($method)
    {
        $this->logger
            ->expects($this->never())
            ->method($method);
    }
}

class TestableLoggerAware
{
    use LoggerAware;

    public function callLogMethod($method, $message, $context)
    {
        $this->$method($message, $context);
    }

    public function callLogException($message, $exception, $context)
    {
        $this->logException($message, $exception, $context);
    }
}
