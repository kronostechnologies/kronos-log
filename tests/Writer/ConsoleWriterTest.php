<?php

namespace Kronos\Tests\Log\Writer;

use Exception;
use Kronos\Log\Adaptor\FileAdaptorFactory;
use Kronos\Log\Adaptor\TTYAdaptor;
use Kronos\Log\Enumeration\AnsiBackgroundColor;
use Kronos\Log\Enumeration\AnsiTextColor;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Logger;
use Kronos\Log\Writer\ConsoleWriter;
use Kronos\Tests\Log\ExtendedTestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

class ConsoleWriterTest extends ExtendedTestCase
{
    const string LOGLEVEL_BELOW_ERROR = LogLevel::INFO;
    const string LOGLEVEL_ABOVE_WARNING = LogLevel::ERROR;
    const string A_MESSAGE = 'a message {key}';
    const string CONTEXT_KEY = 'key';
    const string CONTEXT_VALUE = 'value';
    const string INTERPOLATED_MESSAGE = 'a message value';
    const string INTERPOLATED_MESSAGE_WITH_LOG_LEVEL = 'INFO : a message value';
    const string DATETIME_REGEX = '\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]';

    const string EXCEPTION_MESSAGE = 'Some exception message';
    const string EXCEPTION_FILE = '/tmp/some/file.php';
    const int EXCEPTION_LINE = 2;
    const string EXCEPTION_TITLE_LINE_FORMAT = "Exception: 'Some exception message' in '%s' at line %i";
    const string PREVIOUS_EXCEPTION_MESSAGE = 'Previous exception message';
    const string PREVIOUS_EXCEPTION_FILE = '/tmp/some/other/file.php';
    const int PREVIOUS_EXCEPTION_LINE = 3;
    const string PREVIOUS_EXCEPTION_TITLE_LINE_FORMAT = "Previous exception: 'Previous exception message' in '%s' at line %i";

    private ConsoleWriter $writer;
    private FileAdaptorFactory&MockObject $fileFactory;
    private TTYAdaptor&MockObject $stdout;
    private TTYAdaptor&MockObject $stderr;

    public function setUp(): void
    {
        $this->fileFactory = $this->getMockBuilder(FileAdaptorFactory::class)->disableOriginalConstructor()->getMock();
    }

    #[Test]
    public function newConsole_Constructor_ShouldCreateAdaptorForStdoutAndStderr()
    {
        $this->fileFactory
            ->expects(self::exactly(2))
            ->method('createTTYAdaptor')
            ->with(
                ...self::withConsecutive(
                [ConsoleWriter::STDOUT],
                [ConsoleWriter::STDERR]
            )
            );

        $this->writer = new ConsoleWriter(factory: $this->fileFactory);
    }

    #[Test]
    public function console_LogWithLevelBelowError_ShouldWriteInterpolatedMessageToStdout()
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsWriteToBeCalled($this->stdout, self::INTERPOLATED_MESSAGE);
        $this->writer = new ConsoleWriter(factory: $this->fileFactory);

        $this->writer->log(self::LOGLEVEL_BELOW_ERROR, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function console_LogWarning_ShouldWriteInterpolatedMessageToStdoutInYellow()
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsWriteToBeCalled($this->stdout, self::INTERPOLATED_MESSAGE, AnsiTextColor::YELLOW);
        $this->writer = new ConsoleWriter(factory: $this->fileFactory);

        $this->writer->log(LogLevel::WARNING, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function console_LogWithLevelAboveWarning_ShouldWriteInterpolatedMessageToStderrInWhiteOnRed()
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsWriteToBeCalled($this->stderr, self::INTERPOLATED_MESSAGE, AnsiTextColor::WHITE,
            AnsiBackgroundColor::RED);
        $this->writer = new ConsoleWriter(factory: $this->fileFactory);

        $this->writer->log(self::LOGLEVEL_ABOVE_WARNING, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function consolePrependingLogLevelAndDateTime_LogWithLevelBelowError_ShouldCallWriteWithMessagePrependedByDateTimeThenLogLevel(
    )
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsWriteToBeCalled($this->stdout,
            self::matchesRegularExpression('/' . self::DATETIME_REGEX . self::INTERPOLATED_MESSAGE_WITH_LOG_LEVEL . '/'));
        $this->writer = new ConsoleWriter(factory: $this->fileFactory);
        $this->writer->setPrependLogLevel();
        $this->writer->setPrependDateTime();

        $this->writer->log(self::LOGLEVEL_BELOW_ERROR, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function console_SetForceAnsiColorSupport_ShouldCallSetForceAnsiColorSupportOnStdoutAndStdError()
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsSetForceAnsiColorSupportToBeCalled($this->stdout);
        $this->expectsSetForceAnsiColorSupportToBeCalled($this->stderr);
        $this->writer = new ConsoleWriter(factory: $this->fileFactory);

        $this->writer->setForceAnsiColorSupport();
    }

    #[Test]
    public function console_SetForceNoAnsiColorSupport_ShouldCallSetForceNoAnsiColorSupportOnStdoutAndStdError()
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsSetForceNoAnsiColorSupportToBeCalled($this->stdout);
        $this->expectsSetForceNoAnsiColorSupportToBeCalled($this->stderr);
        $this->writer = new ConsoleWriter(factory: $this->fileFactory);

        $this->writer->setForceNoAnsiColorSupport();
    }

    #[Test]
    public function contextContainingExceptionAndLogLevelLowerThanError_Log_ShouldWriteExceptionWithoutStackTrace()
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsWriteToBeCalledWithConsecutive($this->stdout, [
            [self::INTERPOLATED_MESSAGE],
        ]);
        $this->expectsWriteToBeCalledWithConsecutive($this->stderr, [
            [self::matches(self::EXCEPTION_TITLE_LINE_FORMAT)],
            ['']
        ]);
        $writer = new ConsoleWriter(factory: $this->fileFactory);
        $context = [
            self::CONTEXT_KEY => self::CONTEXT_VALUE,
            Logger::EXCEPTION_CONTEXT => new Exception(self::EXCEPTION_MESSAGE)
        ];

        $writer->log(self::LOGLEVEL_BELOW_ERROR, self::A_MESSAGE, $context);
    }

    #[Test]
    public function contextContainingExceptionAndLogLevelIsErrorAndTraceBuilder_Log_ShouldWriteExceptionMessageAndStackTrace(
    )
    {
        $this->givenFactoryReturnFileAdaptors();
        $exceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $this->expectsWriteToBeCalledWithConsecutive($this->stderr, [
            [self::INTERPOLATED_MESSAGE, AnsiTextColor::WHITE, AnsiBackgroundColor::RED],
            [self::matches(self::EXCEPTION_TITLE_LINE_FORMAT)],
            [self::anything()],
            [''] // Because we can't mock exceptions, can't be sure it's really the stacktrace...
        ]);
        $exception = new Exception(self::EXCEPTION_MESSAGE);
        $exceptionTraceBuilder->expects(self::once())
            ->method('getTraceAsString')
            ->willReturn($exception->getTraceAsString());

        $writer = new ConsoleWriter($exceptionTraceBuilder, factory: $this->fileFactory);
        $context = [
            self::CONTEXT_KEY => self::CONTEXT_VALUE,
            Logger::EXCEPTION_CONTEXT => $exception
        ];

        $writer->log(LogLevel::ERROR, self::A_MESSAGE, $context);
    }

    #[Test]
    public function contextContainingExceptionAndLogLevelIsErrorAndNoTraceBuilder_Log_ShouldWriteExceptionMessage()
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsWriteToBeCalledWithConsecutive($this->stderr, [
            [self::INTERPOLATED_MESSAGE, AnsiTextColor::WHITE, AnsiBackgroundColor::RED],
            [self::matches(self::EXCEPTION_TITLE_LINE_FORMAT)],
            ['']
        ]);
        $exception = new Exception(self::EXCEPTION_MESSAGE);

        $writer = new ConsoleWriter(factory: $this->fileFactory);
        $context = [
            self::CONTEXT_KEY => self::CONTEXT_VALUE,
            Logger::EXCEPTION_CONTEXT => $exception
        ];

        $writer->log(LogLevel::ERROR, self::A_MESSAGE, $context);
    }

    #[Test]
    public function contextContainingExceptionWithPreviousExceptionAndLogLevelIsErrorAndPreviousExceptionTraceBuilder_Log_ShouldWriteMessageAndStacktraceForPreviousException(
    )
    {
        $this->givenFactoryReturnFileAdaptors();
        $previousExceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $previous_exception = new Exception(self::PREVIOUS_EXCEPTION_MESSAGE);
        $exception = new Exception(self::EXCEPTION_MESSAGE, 0, $previous_exception);
        $this->expectsWriteToBeCalledWithConsecutive($this->stderr, [
            [self::INTERPOLATED_MESSAGE, AnsiTextColor::WHITE, AnsiBackgroundColor::RED],
            [self::matches(self::EXCEPTION_TITLE_LINE_FORMAT)],
            [''],
            [self::matches(self::PREVIOUS_EXCEPTION_TITLE_LINE_FORMAT)],
            [$previous_exception->getTraceAsString()],
            ['']
        ]);
        $previousExceptionTraceBuilder->expects(self::once())
            ->method('getTraceAsString')
            ->willReturn($previous_exception->getTraceAsString());

        $writer = new ConsoleWriter(previousExceptionTraceBuilder: $previousExceptionTraceBuilder, factory: $this->fileFactory);
        $context = [
            self::CONTEXT_KEY => self::CONTEXT_VALUE,
            Logger::EXCEPTION_CONTEXT => $exception
        ];

        $writer->log(LogLevel::ERROR, self::A_MESSAGE, $context);
    }

    #[Test]
    public function contextContainingExceptionWithPreviousExceptionAndLogLevelIsError_Log_ShouldWriteMessageForPreviousException(
    )
    {
        $this->givenFactoryReturnFileAdaptors();
        $previous_exception = new Exception(self::PREVIOUS_EXCEPTION_MESSAGE);
        $exception = new Exception(self::EXCEPTION_MESSAGE, 0, $previous_exception);
        $this->expectsWriteToBeCalledWithConsecutive($this->stderr, [
            [self::INTERPOLATED_MESSAGE, AnsiTextColor::WHITE, AnsiBackgroundColor::RED],
            [self::matches(self::EXCEPTION_TITLE_LINE_FORMAT)],
            [''],
            [self::matches(self::PREVIOUS_EXCEPTION_TITLE_LINE_FORMAT)],
            ['']
        ]);

        $writer = new ConsoleWriter(factory: $this->fileFactory);
        $context = [
            self::CONTEXT_KEY => self::CONTEXT_VALUE,
            Logger::EXCEPTION_CONTEXT => $exception
        ];

        $writer->log(LogLevel::ERROR, self::A_MESSAGE, $context);
    }

    private function givenFactoryReturnFileAdaptors()
    {
        $this->stdout = $this->getMockBuilder(TTYAdaptor::class)->disableOriginalConstructor()->getMock();
        $this->stderr = $this->getMockBuilder(TTYAdaptor::class)->disableOriginalConstructor()->getMock();

        $this->fileFactory
            ->method('createTTYAdaptor')
            ->willReturnMap([
                [ConsoleWriter::STDOUT, $this->stdout],
                [ConsoleWriter::STDERR, $this->stderr],
            ]);
    }

    private function expectsWriteToBeCalled(
        TTYAdaptor&MockObject $file,
        $message,
        $text_color = null,
        $background_color = null
    ) {
        $file->expects(self::once())->method('write')->with($message, $text_color, $background_color);
    }

    private function expectsSetForceAnsiColorSupportToBeCalled(TTYAdaptor&MockObject $file)
    {
        $file->expects(self::once())->method('setForceAnsiColorSupport')->with(true);
    }

    private function expectsSetForceNoAnsiColorSupportToBeCalled(TTYAdaptor&MockObject $file)
    {
        $file->expects(self::once())->method('setForceNoAnsiColorSupport')->with(true);
    }

    private function expectsWriteToBeCalledWithConsecutive(TTYAdaptor&MockObject $file, array $consecutive_args)
    {
        $file
            ->expects(self::exactly(count($consecutive_args)))
            ->method('write')
            ->with(
                ...self::withConsecutive(
                ...$consecutive_args
            )
            );
    }
}
