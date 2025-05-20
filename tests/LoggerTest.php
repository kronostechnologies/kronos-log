<?php

namespace Kronos\Tests\Log;

use Kronos\Log\Logger;
use Kronos\Log\WriterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LoggerTest extends TestCase
{
    private const ANY_LOG_LEVEL = LogLevel::INFO;
    private const A_MESSAGE = 'some messge';
    private const A_CONTEXT_KEY = 'key';
    private const A_CONTEXT_VALUE = 'value';
    private const ANOTHER_CONTEXT_KEY = 'another key';
    private const ANOTHER_CONTEXT_VALUE = 'another value';
    private const WRITER_LOG_EXCEPTION_MESSAGE = 'Writer log exception message';

    private Logger $logger;
    private WriterInterface & MockObject $writer;

    public function setUp(): void
    {
        $this->writer = $this->createMock(WriterInterface::class);

        $this->logger = new Logger();
        $this->logger->addWriter($this->writer);
    }

    public function test_LoggerWithWriter_Log_ShouldAskWriteCanLogLevel(): void
    {
        $this->writer
            ->expects(self::once())
            ->method('canLogLevel')
            ->with(self::ANY_LOG_LEVEL);

        $this->logger->log(self::ANY_LOG_LEVEL, self::A_MESSAGE);
    }

    public function test_LoggerWithWriterThatCanLog_Log_ShouldTellWriterToLog(): void
    {
        $this->givenWriterCanLog();
        $this->expectsWriterLogToBeCalledWith(self::ANY_LOG_LEVEL, self::A_MESSAGE,
            [self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE]);

        $this->logger->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, [self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE]);
    }

    public function test_LoggerWithWritterThatCannotLog_Log_ShouldNotCallLogOnWriter(): void
    {
        $this->writer->method('canLogLevel')->willReturn(false);
        $this->writer->expects(self::never())->method('log');

        $this->logger->log(self::ANY_LOG_LEVEL, self::A_MESSAGE);
    }

    public function test_LoggerWithContextAndWriter_Log_ShouldAddGivenContext(): void
    {
        $this->givenWriterCanLog();
        $this->logger->addContext(self::ANOTHER_CONTEXT_KEY, self::ANOTHER_CONTEXT_VALUE);
        $this->expectsWriterLogToBeCalledWith(
            self::ANY_LOG_LEVEL,
            self::A_MESSAGE,
            [
                self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE,
                self::ANOTHER_CONTEXT_KEY => self::ANOTHER_CONTEXT_VALUE
            ]
        );

        $this->logger->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, [self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE]);
    }

    public function test_LoggerContextArrayAndWriter_Log_ShouldAddGivenContext(): void
    {
        $this->givenWriterCanLog();
        $this->logger->addContextArray([self::ANOTHER_CONTEXT_KEY => self::ANOTHER_CONTEXT_VALUE]);
        $this->expectsWriterLogToBeCalledWith(
            self::ANY_LOG_LEVEL,
            self::A_MESSAGE,
            [
                self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE,
                self::ANOTHER_CONTEXT_KEY => self::ANOTHER_CONTEXT_VALUE
            ]
        );

        $this->logger->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, [self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE]);
    }

    public function test_WriterThrowException_Log_ShouldCatchExceptionAndTriggerError(): void
    {
        $errorHandled = 0;
        $handledTriggedError = false;
        set_error_handler(function ($errno, $errstr) use (
            &$handledTriggedError,
            &$errorHandled
        ) {
            $errorHandled++;
            $handledTriggedError = ($errno == E_USER_ERROR && $errstr == self::WRITER_LOG_EXCEPTION_MESSAGE);
        });

        try {
            $this->givenWriterCanLog();
            $this->writer->method('log')->willThrowException(new \Exception(self::WRITER_LOG_EXCEPTION_MESSAGE));

            $this->logger->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, [self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE]);

            self::assertGreaterThan(1, $errorHandled);
            self::assertTrue($handledTriggedError);
        }
        finally {
            // making sure that no matter what happens in my test, PHPUnit error handler is put back
            restore_error_handler();
        }
    }

    public function test_exception_logExceptionWithContext(): void
    {
        $this->givenWriterCanLog();
        $exception = new \RuntimeException("Eception message");

        $this->expectsWriterLogToBeCalledWith(
            LogLevel::ERROR,
            self::A_MESSAGE,
            [
                Logger::EXCEPTION_CONTEXT => $exception
            ]
        );

        $this->logger->exception(self::A_MESSAGE, $exception);
    }

    private function givenWriterCanLog(): void
    {
        $this->writer->method('canLogLevel')->willReturn(true);
    }

    private function expectsWriterLogToBeCalledWith($level, $message, $context): void
    {
        $this->writer
            ->expects(self::once())
            ->method('log')
            ->with($level, $message, $context);
    }
}
