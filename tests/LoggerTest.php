<?php

namespace Kronos\Tests\Log;

use Kronos\Log\Logger;
use Kronos\Log\WriterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

class LoggerTest extends \PHPUnit\Framework\TestCase
{

    const ANY_LOG_LEVEL = LogLevel::INFO;
    const A_MESSAGE = 'some messge';
    const A_CONTEXT_KEY = 'key';
    const A_CONTEXT_VALUE = 'value';
    const ANOTHER_CONTEXT_KEY = 'another key';
    const ANOTHER_CONTEXT_VALUE = 'another value';
    const WRITER_LOG_EXCEPTION_MESSAGE = 'Writer log exception message';


    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var MockObject&WriterInterface
     */
    private $writer;

    public function setUp(): void
    {
        $this->writer = $this->createMock(WriterInterface::class);

        $this->logger = new Logger();
        $this->logger->addWriter($this->writer);
    }

    public function test_LoggerWithWriter_Log_ShouldAskWriteCanLogLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('canLogLevel')
            ->with(self::ANY_LOG_LEVEL);

        $this->logger->log(self::ANY_LOG_LEVEL, self::A_MESSAGE);
    }

    public function test_LoggerWithWriterThatCanLog_Log_ShouldTellWriterToLog()
    {
        $this->givenWriterCanLog();
        $this->expectsWriterLogToBeCalledWith(self::ANY_LOG_LEVEL, self::A_MESSAGE,
            [self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE]);

        $this->logger->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, [self::A_CONTEXT_KEY => self::A_CONTEXT_VALUE]);
    }

    public function test_LoggerWithWritterThatCannotLog_Log_ShouldNotCallLogOnWriter()
    {
        $this->writer->method('canLogLevel')->willReturn(false);
        $this->writer->expects(self::never())->method('log');

        $this->logger->log(self::ANY_LOG_LEVEL, self::A_MESSAGE);
    }

    public function test_LoggerWithContextAndWriter_Log_ShouldAddGivenContext()
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

    public function test_LoggerContextArrayAndWriter_Log_ShouldAddGivenContext()
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

    public function test_WriterThrowException_Log_ShouldCatchExceptionAndTriggerError()
    {
        $errorHandled = 0;
        $handledTriggedError = false;
        $previousErrorHandler = set_error_handler(function ($errno, $errstr) use (
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

            self::assertEquals(1, $errorHandled);
            self::assertTrue($handledTriggedError);
        }
        finally {
            // making sure that no matter what happens in my test, PHPUnit error handler is put back
            set_error_handler($previousErrorHandler);
        }
    }

    private function givenWriterCanLog()
    {
        $this->writer->method('canLogLevel')->willReturn(true);
    }

    private function expectsWriterLogToBeCalledWith($level, $message, $context)
    {
        $this->writer
            ->expects(self::once())
            ->method('log')
            ->with($level, $message, $context);
    }
}
