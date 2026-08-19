<?php

namespace Kronos\Tests\Log\Writer;

use Kronos\Log\Writer\SentryWriter;
use Kronos\Log\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Sentry\ClientInterface;
use Sentry\Severity;
use Sentry\State\Scope;
use Sentry\Tracing\PropagationContext;
use Sentry\Tracing\SpanId;
use Sentry\Tracing\TraceId;

class SentryWriterTest extends TestCase
{
    const string A_MESSAGE = 'a message';
    const string INTERPOLATABLE_MESSAGE = 'message with {key}';
    const string INTERPOLATED_MESSAGE = 'message with value';
    const string CONTEXT_KEY = 'key';
    const string CONTEXT_VALUE = 'value';
    const string ANY_LEVEL = LogLevel::DEBUG;
    const string LOGGER_MESSAGE_KEY = 'loggerMessage';

    private ClientInterface&MockObject $sentryClient;
    private SentryWriter | SentryWriterWithScopeDecorator $writer;

    public function setUp(): void
    {
        $this->sentryClient = $this->createMock(ClientInterface::class);

        $this->writer = new SentryWriterWithScopeDecorator($this->sentryClient);
    }

    #[Test]
    public function messageAndContext_Log_SouldCallCaptureMessageWithInterpolatedMessage()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::INTERPOLATED_MESSAGE, $this->anything());

        $this->writer->log(self::ANY_LEVEL, self::INTERPOLATABLE_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function debugLevel_Log_ShouldCaptureMessageWithDebugLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith($this->anything(), Severity::DEBUG);

        $this->writer->log(LogLevel::DEBUG, self::A_MESSAGE);
    }

    #[Test]
    public function infoLevel_Log_ShouldCaptureMessageWithInfoLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, Severity::INFO);

        $this->writer->log(LogLevel::INFO, self::A_MESSAGE);
    }

    #[Test]
    public function noticeLevel_Log_ShouldCaptureMessageWithInfoLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, Severity::INFO);

        $this->writer->log(LogLevel::NOTICE, self::A_MESSAGE);
    }

    #[Test]
    public function warningLevel_Log_ShouldCaptureMessageWithWarningLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, Severity::WARNING);

        $this->writer->log(LogLevel::WARNING, self::A_MESSAGE);
    }

    #[Test]
    public function errorLevel_Log_ShouldCaptureMessageWithErrorLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, Severity::ERROR);

        $this->writer->log(LogLevel::ERROR, self::A_MESSAGE);
    }

    #[Test]
    public function criticalLevel_Log_ShouldCaptureMessageWithFatalLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, Severity::FATAL);

        $this->writer->log(LogLevel::CRITICAL, self::A_MESSAGE);
    }

    #[Test]
    public function alertLevel_Log_ShouldCaptureMessageWithFatalLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, Severity::FATAL);

        $this->writer->log(LogLevel::ALERT, self::A_MESSAGE);
    }

    #[Test]
    public function emergencyLevel_Log_ShouldCaptureMessageWithFatalLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, Severity::FATAL);

        $this->writer->log(LogLevel::EMERGENCY, self::A_MESSAGE);
    }

    #[Test]
    public function messageAndContext_Log_ShouldCallCaptureMessageWithContextAsExtra()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, Severity::DEBUG);

        $this->writer->log(LogLevel::DEBUG, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function contextWithException_Log_ShouldCallCaptureException()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, Severity::debug(),
            [self::LOGGER_MESSAGE_KEY => self::A_MESSAGE]);

        $this->writer->log(self::ANY_LEVEL, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    #[Test]
    public function contextWithExceptionAndOtherKeys_Log_ShouldCaptureExceptionWithContextAsExtraWithoutException()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, Severity::debug(), [
            self::CONTEXT_KEY => self::CONTEXT_VALUE,
            self::LOGGER_MESSAGE_KEY => self::A_MESSAGE
        ]);

        $this->writer->log(LogLevel::DEBUG, self::A_MESSAGE,
            [Logger::EXCEPTION_CONTEXT => $exception, self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function contextWithExceptionAndDebugLevel_Log_ShouldCaptureExceptionWithDebugLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($this->anything(), Severity::debug(), [
            self::LOGGER_MESSAGE_KEY => self::A_MESSAGE
        ]);

        $this->writer->log(LogLevel::DEBUG, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    #[Test]
    public function contextWithExceptionAndInfoLevel_Log_ShouldCaptureExceptionWithInfoLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, Severity::info(), [
            self::LOGGER_MESSAGE_KEY => self::A_MESSAGE
        ]);

        $this->writer->log(LogLevel::INFO, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    #[Test]
    public function contextWithExceptionAndNoticeLevel_Log_ShouldCaptureExceptionWithInfoLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, Severity::info(), [
            self::LOGGER_MESSAGE_KEY => self::A_MESSAGE
        ]);

        $this->writer->log(LogLevel::NOTICE, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    #[Test]
    public function contextWithExceptionAndWarningLevel_Log_ShouldCaptureExceptionWithWarningLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, Severity::warning(), [
            self::LOGGER_MESSAGE_KEY => self::A_MESSAGE
        ]);

        $this->writer->log(LogLevel::WARNING, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    #[Test]
    public function contextWithExceptionAndErrorLevel_Log_ShouldCaptureExceptionWithErrorLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, Severity::error(), [
            self::LOGGER_MESSAGE_KEY => self::A_MESSAGE
        ]);

        $this->writer->log(LogLevel::ERROR, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    #[Test]
    public function contextWithExceptionAndCriticalLevel_Log_ShouldCaptureExceptionWithErrorLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, Severity::fatal(), [
            self::LOGGER_MESSAGE_KEY => self::A_MESSAGE
        ]);

        $this->writer->log(LogLevel::CRITICAL, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    #[Test]
    public function contextWithExceptionAndAlertLevel_Log_ShouldCaptureExceptionWithFatalLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, Severity::fatal(), [
            self::LOGGER_MESSAGE_KEY => self::A_MESSAGE
        ]);

        $this->writer->log(LogLevel::ALERT, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    #[Test]
    public function contextWithExceptionAndEmergencyLevel_Log_ShouldCaptureExceptionWithFatalLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, Severity::fatal(), [
            self::LOGGER_MESSAGE_KEY => self::A_MESSAGE
        ]);

        $this->writer->log(LogLevel::EMERGENCY, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    private function expectsCaptureMessageToBeCalledWith($message, $level)
    {
        $this->sentryClient
            ->expects($this->once())
            ->method('captureMessage')
            ->with($message, $level);
    }

    private function expectsCaptureExceptionToBeCalledWith($exception, $level, $params = [])
    {
        $this->sentryClient
            ->expects($this->once())
            ->method('captureException')
            ->with(
                $exception,
                $this->callback(
                    function (Scope $scope) use ($level, $params) {
                        $scopeReflection = new \ReflectionObject($scope);
                        $scopeLevel = $scopeReflection->getProperty('level')->getValue($scope);
                        $scopeExtra = $scopeReflection->getProperty('extra')->getValue($scope);
                        $this->assertEquals($level, $scopeLevel);
                        $this->assertEquals($params, $scopeExtra);
                        return true;
                    }
                )
            );
    }
}

class SentryWriterWithScopeDecorator extends SentryWriter
{
    const SPAN_ID = "3d1bf6350d09fb80";
    const TRACE_ID = "141bb800f59d073b7a075b1eed7d5372";

    public function log($level, $message, array $context = [])
    {
        parent::log($level, $message, $context);
    }

    protected function getSentryScope($level, $context): Scope
    {
        $scope = new Scope();
        $scope->setLevel($level);
        $this->setPropagationContext($scope);

        if (count($context)) {
            $scope->setExtras($context);
        }

        return $scope;
    }

    private function setPropagationContext(Scope $scope): void
    {
        $propagationContext = PropagationContext::fromDefaults();
        $propagationContext->setSpanId(new SpanId(self::SPAN_ID));
        $propagationContext->setTraceId(new TraceId(self::TRACE_ID));

        $scope->setPropagationContext($propagationContext);
    }
}
