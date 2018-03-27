<?php

namespace Kronos\Tests\Log\Writer;

use Kronos\Log\Writer\Sentry;
use Kronos\Log\Logger;
use Psr\Log\LogLevel;

class SentryTest extends \PHPUnit_Framework_TestCase
{

    const A_MESSAGE = 'a message';
    const INTERPOLATABLE_MESSAGE = 'message with {key}';
    const INTERPOLATED_MESSAGE = 'message with value';
    const CONTEXT_KEY = 'key';
    const CONTEXT_VALUE = 'value';
    const ANY_LEVEL = LogLevel::DEBUG;

    private $raven_client;

    /**
     * @var Sentry
     */
    private $writer;

    public function setUp()
    {
        $this->raven_client = $this->getMockWithoutInvokingTheOriginalConstructor(\Raven_Client::class);

        $this->writer = new Sentry($this->raven_client);
    }

    public function test_MessageAndContext_Log_SouldCallCaptureMessageWithInterpolatedMessage()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::INTERPOLATED_MESSAGE, $this->anything());

        $this->writer->log(self::ANY_LEVEL, self::INTERPOLATABLE_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    public function test_DebugLevel_Log_ShouldCaptureMessageWithDebugLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith($this->anything(), ['level' => \Raven_Client::DEBUG]);

        $this->writer->log(LogLevel::DEBUG, self::A_MESSAGE);
    }

    public function test_InfoLevel_Log_ShouldCaptureMessageWithInfoLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, ['level' => \Raven_Client::INFO]);

        $this->writer->log(LogLevel::INFO, self::A_MESSAGE);
    }

    public function test_NoticeLevel_Log_ShouldCaptureMessageWithInfoLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, ['level' => \Raven_Client::INFO]);

        $this->writer->log(LogLevel::NOTICE, self::A_MESSAGE);
    }

    public function test_WarningLevel_Log_ShouldCaptureMessageWithWarningLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, ['level' => \Raven_Client::WARNING]);

        $this->writer->log(LogLevel::WARNING, self::A_MESSAGE);
    }

    public function test_ErrorLevel_Log_ShouldCaptureMessageWithErrorLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, ['level' => \Raven_Client::ERROR]);

        $this->writer->log(LogLevel::ERROR, self::A_MESSAGE);
    }

    public function test_CriticalLevel_Log_ShouldCaptureMessageWithErrorLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, ['level' => \Raven_Client::ERROR]);

        $this->writer->log(LogLevel::CRITICAL, self::A_MESSAGE);
    }

    public function test_AlertLevel_Log_ShouldCaptureMessageWithFatalLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, ['level' => \Raven_Client::FATAL]);

        $this->writer->log(LogLevel::ALERT, self::A_MESSAGE);
    }

    public function test_EmergencyLevel_Log_ShouldCaptureMessageWithFatalLevel()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE, ['level' => \Raven_Client::FATAL]);

        $this->writer->log(LogLevel::EMERGENCY, self::A_MESSAGE);
    }

    public function test_MessageAndContext_Log_ShouldCallCaptureMessageWithContextAsExtra()
    {
        $this->expectsCaptureMessageToBeCalledWith(self::A_MESSAGE,
            ['level' => \Raven_Client::DEBUG, 'extra' => [self::CONTEXT_KEY => self::CONTEXT_VALUE]]);

        $this->writer->log(LogLevel::DEBUG, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    public function test_ContextWithException_Log_ShouldCallCaptureException()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, $this->anything());

        $this->writer->log(self::ANY_LEVEL, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    public function test_ContextWithExceptionAndOtherKeys_Log_ShouldCaptureExceptionWithContextAsExtraWithoutException()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception,
            ['level' => \Raven_Client::DEBUG, 'extra' => [self::CONTEXT_KEY => self::CONTEXT_VALUE]]);

        $this->writer->log(LogLevel::DEBUG, self::A_MESSAGE,
            [Logger::EXCEPTION_CONTEXT => $exception, self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    public function test_ContextWithExceptionAndDebugLevel_Log_ShouldCaptureExceptionWithDebugLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($this->anything(), ['level' => \Raven_Client::DEBUG]);

        $this->writer->log(LogLevel::DEBUG, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    public function test_ContextWithExceptionAndInfoLevel_Log_ShouldCaptureExceptionWithInfoLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, ['level' => \Raven_Client::INFO]);

        $this->writer->log(LogLevel::INFO, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    public function test_ContextWithExceptionAndNoticeLevel_Log_ShouldCaptureExceptionWithInfoLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, ['level' => \Raven_Client::INFO]);

        $this->writer->log(LogLevel::NOTICE, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    public function test_ContextWithExceptionAndWarningLevel_Log_ShouldCaptureExceptionWithWarningLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, ['level' => \Raven_Client::WARNING]);

        $this->writer->log(LogLevel::WARNING, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    public function test_ContextWithExceptionAndErrorLevel_Log_ShouldCaptureExceptionWithErrorLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, ['level' => \Raven_Client::ERROR]);

        $this->writer->log(LogLevel::ERROR, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    public function test_ContextWithExceptionAndCriticalLevel_Log_ShouldCaptureExceptionWithErrorLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, ['level' => \Raven_Client::ERROR]);

        $this->writer->log(LogLevel::CRITICAL, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    public function test_ContextWithExceptionAndAlertLevel_Log_ShouldCaptureExceptionWithFatalLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, ['level' => \Raven_Client::FATAL]);

        $this->writer->log(LogLevel::ALERT, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    public function test_ContextWithExceptionAndEmergencyLevel_Log_ShouldCaptureExceptionWithFatalLevel()
    {
        $exception = new \Exception(self::A_MESSAGE);
        $this->expectsCaptureExceptionToBeCalledWith($exception, ['level' => \Raven_Client::FATAL]);

        $this->writer->log(LogLevel::EMERGENCY, self::A_MESSAGE, [Logger::EXCEPTION_CONTEXT => $exception]);
    }

    private function expectsCaptureMessageToBeCalledWith($message, $params)
    {
        $this->raven_client
            ->expects($this->once())
            ->method('captureMessage')
            ->with($message, [], $params);
    }

    private function expectsCaptureExceptionToBeCalledWith($exception, $params)
    {
        $this->raven_client
            ->expects($this->once())
            ->method('captureException')
            ->with($exception, $params);
    }
}
