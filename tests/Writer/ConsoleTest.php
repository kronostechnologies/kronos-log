<?php

namespace Kronos\Tests\Log\Writer;

use Kronos\Log\Adaptor\FileFactory;
use Kronos\Log\Adaptor\TTY;
use Kronos\Log\Enumeration\AnsiBackgroundColor;
use Kronos\Log\Enumeration\AnsiTextColor;
use Kronos\Log\Factory\Formatter;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Writer\Console;
use \Kronos\Log\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;
use Exception;
use PHPUnit_Framework_MockObject_MockObject;

class ConsoleTest extends \PHPUnit\Framework\TestCase
{

    const LOGLEVEL_BELOW_ERROR = LogLevel::INFO;
    const LOGLEVEL_ABOVE_WARNING = LogLevel::ERROR;
    const A_MESSAGE = 'a message {key}';
    const CONTEXT_KEY = 'key';
    const CONTEXT_VALUE = 'value';
    const INTERPOLATED_MESSAGE = 'a message value';
    const INTERPOLATED_MESSAGE_WITH_LOG_LEVEL = 'INFO : a message value';
    const DATETIME_REGEX = '\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]';


    const EXCEPTION_MESSAGE = 'Some exception message';
    const EXCEPTION_FILE = '/tmp/some/file.php';
    const EXCEPTION_LINE = 2;
    const EXCEPTION_TITLE_LINE_FORMAT = "Exception: 'Some exception message' in '%s' at line %i";
    const PREVIOUS_EXCEPTION_MESSAGE = 'Previous exception message';
    const PREVIOUS_EXCEPTION_FILE = '/tmp/some/other/file.php';
    const PREVIOUS_EXCEPTION_LINE = 3;
    const PREVIOUS_EXCEPTION_TITLE_LINE_FORMAT = "Previous exception: 'Previous exception message' in '%s' at line %i";

    /**
     * @var Console
     */
    private $writer;

    /**
     * @var FileFactory&MockObject
     */
    private $fileFactory;

    /**
     * @var TTY&MockObject
     */
    private $stdout;

    /**
     * @var TTY&MockObject
     */
    private $stderr;

    /**
     * @var TraceBuilder&MockObject
     */
    private $exceptionTraceBuilder;

    /**
     * @var TraceBuilder&MockObject
     */
    private $previousExceptionTraceBuilder;


    public function setUp(): void
    {
        $this->fileFactory = $this->getMockBuilder(FileFactory::class)->disableOriginalConstructor()->getMock();
    }

    public function test_NewConsole_Constructor_ShouldCreateAdaptorForStdoutAndStderr()
    {
        $this->fileFactory
            ->expects(self::exactly(2))
            ->method('createTTYAdaptor')
            ->withConsecutive(
                [Console::STDOUT],
                [Console::STDERR]
            );

        $this->writer = new Console($this->fileFactory);
    }

    public function test_Console_LogWithLevelBelowError_ShouldWriteInterpolatedMessageToStdout()
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsWriteToBeCalled($this->stdout, self::INTERPOLATED_MESSAGE);
        $this->writer = new Console($this->fileFactory);

        $this->writer->log(self::LOGLEVEL_BELOW_ERROR, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    public function test_Console_LogWarning_ShouldWriteInterpolatedMessageToStdoutInYellow()
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsWriteToBeCalled($this->stdout, self::INTERPOLATED_MESSAGE, AnsiTextColor::YELLOW);
        $this->writer = new Console($this->fileFactory);

        $this->writer->log(LogLevel::WARNING, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    public function test_Console_LogWithLevelAboveWarning_ShouldWriteInterpolatedMessageToStderrInWhiteOnRed()
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsWriteToBeCalled($this->stderr, self::INTERPOLATED_MESSAGE, AnsiTextColor::WHITE,
            AnsiBackgroundColor::RED);
        $this->writer = new Console($this->fileFactory);

        $this->writer->log(self::LOGLEVEL_ABOVE_WARNING, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    public function test_ConsolePrependingLogLevelAndDateTime_LogWithLevelBelowError_ShouldCallWriteWithMessagePrependedByDateTimeThenLogLevel(
    )
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsWriteToBeCalled($this->stdout,
            self::matchesRegularExpression('/' . self::DATETIME_REGEX . '' . self::INTERPOLATED_MESSAGE_WITH_LOG_LEVEL . '/'));
        $this->writer = new Console($this->fileFactory);
        $this->writer->setPrependLogLevel();
        $this->writer->setPrependDateTime();

        $this->writer->log(self::LOGLEVEL_BELOW_ERROR, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    public function test_Console_SetForceAnsiColorSupport_ShouldCallSetForceAnsiColorSupportOnStdoutAndStdError()
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsSetForceAnsiColorSupportToBeCalled($this->stdout, true);
        $this->expectsSetForceAnsiColorSupportToBeCalled($this->stderr, true);
        $this->writer = new Console($this->fileFactory);

        $this->writer->setForceAnsiColorSupport();
    }

    public function test_Console_SetForceNoAnsiColorSupport_ShouldCallSetForceNoAnsiColorSupportOnStdoutAndStdError()
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsSetForceNoAnsiColorSupportToBeCalled($this->stdout, true);
        $this->expectsSetForceNoAnsiColorSupportToBeCalled($this->stderr, true);
        $this->writer = new Console($this->fileFactory);

        $this->writer->setForceNoAnsiColorSupport();
    }

    public function test_ContextContainingExceptionAndLogLevelLowerThanError_Log_ShouldWriteExceptionWithoutStackTrace()
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsWriteToBeCalledWithConsecutive($this->stdout, [
            [self::INTERPOLATED_MESSAGE],
        ]);
        $this->expectsWriteToBeCalledWithConsecutive($this->stderr, [
            [self::matches(self::EXCEPTION_TITLE_LINE_FORMAT)],
            ['']
        ]);
        $writer = new Console($this->fileFactory);
        $context = [
            self::CONTEXT_KEY => self::CONTEXT_VALUE,
            Logger::EXCEPTION_CONTEXT => new \Exception(self::EXCEPTION_MESSAGE)
        ];

        $writer->log(self::LOGLEVEL_BELOW_ERROR, self::A_MESSAGE, $context);
    }

    public function test_ContextContainingExceptionAndLogLevelIsErrorAndTraceBuilder_Log_ShouldWriteExceptionMessageAndStackTrace(
    )
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->exceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $this->expectsWriteToBeCalledWithConsecutive($this->stderr, [
            [self::INTERPOLATED_MESSAGE, AnsiTextColor::WHITE, AnsiBackgroundColor::RED],
            [self::matches(self::EXCEPTION_TITLE_LINE_FORMAT)],
            [self::anything()],
            [''] // Because we can't mock exceptions, can't be sure it's really the stacktrace...
        ]);
        $exception = new Exception(self::EXCEPTION_MESSAGE);
        $this->exceptionTraceBuilder->expects(self::once())
            ->method('getTraceAsString')
            ->willReturn($exception->getTraceAsString());

        $writer = new Console($this->fileFactory, $this->exceptionTraceBuilder);
        $context = [
            self::CONTEXT_KEY => self::CONTEXT_VALUE,
            Logger::EXCEPTION_CONTEXT => $exception
        ];

        $writer->log(LogLevel::ERROR, self::A_MESSAGE, $context);
    }

    public function test_ContextContainingExceptionAndLogLevelIsErrorAndNoTraceBuilder_Log_ShouldWriteExceptionMessage()
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->expectsWriteToBeCalledWithConsecutive($this->stderr, [
            [self::INTERPOLATED_MESSAGE, AnsiTextColor::WHITE, AnsiBackgroundColor::RED],
            [self::matches(self::EXCEPTION_TITLE_LINE_FORMAT)],
            ['']
        ]);
        $exception = new Exception(self::EXCEPTION_MESSAGE);

        $writer = new Console($this->fileFactory);
        $context = [
            self::CONTEXT_KEY => self::CONTEXT_VALUE,
            Logger::EXCEPTION_CONTEXT => $exception
        ];

        $writer->log(LogLevel::ERROR, self::A_MESSAGE, $context);
    }

    public function test_ContextContainingExceptionWithPreviousExceptionAndLogLevelIsErrorAndPreviouxExceptionTraceBuilder_Log_ShouldWriteMessageAndStacktraceForPreviousException(
    )
    {
        $this->givenFactoryReturnFileAdaptors();
        $this->previousExceptionTraceBuilder = $this->createMock(TraceBuilder::class);
        $previous_exception = new \Exception(self::PREVIOUS_EXCEPTION_MESSAGE);
        $exception = new \Exception(self::EXCEPTION_MESSAGE, 0, $previous_exception);
        $this->expectsWriteToBeCalledWithConsecutive($this->stderr, [
            [self::INTERPOLATED_MESSAGE, AnsiTextColor::WHITE, AnsiBackgroundColor::RED],
            [self::matches(self::EXCEPTION_TITLE_LINE_FORMAT)],
            [''],
            [self::matches(self::PREVIOUS_EXCEPTION_TITLE_LINE_FORMAT)],
            [$previous_exception->getTraceAsString()],
            ['']
        ]);
        $this->previousExceptionTraceBuilder->expects(self::once())
            ->method('getTraceAsString')
            ->willReturn($previous_exception->getTraceAsString());

        $writer = new Console($this->fileFactory, null, $this->previousExceptionTraceBuilder);
        $context = [
            self::CONTEXT_KEY => self::CONTEXT_VALUE,
            Logger::EXCEPTION_CONTEXT => $exception
        ];

        $writer->log(LogLevel::ERROR, self::A_MESSAGE, $context);
    }

    public function test_ContextContainingExceptionWithPreviousExceptionAndLogLevelIsError_Log_ShouldWriteMessageForPreviousException(
    )
    {
        $this->givenFactoryReturnFileAdaptors();
        $previous_exception = new \Exception(self::PREVIOUS_EXCEPTION_MESSAGE);
        $exception = new \Exception(self::EXCEPTION_MESSAGE, 0, $previous_exception);
        $this->expectsWriteToBeCalledWithConsecutive($this->stderr, [
            [self::INTERPOLATED_MESSAGE, AnsiTextColor::WHITE, AnsiBackgroundColor::RED],
            [self::matches(self::EXCEPTION_TITLE_LINE_FORMAT)],
            [''],
            [self::matches(self::PREVIOUS_EXCEPTION_TITLE_LINE_FORMAT)],
            ['']
        ]);

        $writer = new Console($this->fileFactory);
        $context = [
            self::CONTEXT_KEY => self::CONTEXT_VALUE,
            Logger::EXCEPTION_CONTEXT => $exception
        ];

        $writer->log(LogLevel::ERROR, self::A_MESSAGE, $context);
    }

    private function givenFactoryReturnFileAdaptors()
    {
        $this->stdout = $this->getMockBuilder(TTY::class)->disableOriginalConstructor()->getMock();
        $this->stderr = $this->getMockBuilder(TTY::class)->disableOriginalConstructor()->getMock();

        $this->fileFactory
            ->method('createTTYAdaptor')
            ->will(self::returnValueMap([
                [Console::STDOUT, $this->stdout],
                [Console::STDERR, $this->stderr],
            ]));
    }

    /**
     * @param TTY&MockObject $file
     * @param $message
     * @param null $text_color
     * @param null $background_color
     */
    private function expectsWriteToBeCalled($file, $message, $text_color = null, $background_color = null)
    {
        $file->expects(self::once())->method('write')->with($message, $text_color, $background_color);
    }

    /**
     * @param TTY&MockObject $file
     * @param $with
     */
    private function expectsSetForceAnsiColorSupportToBeCalled($file, $with)
    {
        $file->expects(self::once())->method('setForceAnsiColorSupport')->with($with);
    }

    /**
     * @param TTY&MockObject $file
     * @param $with
     */
    private function expectsSetForceNoAnsiColorSupportToBeCalled($file, $with)
    {
        $file->expects(self::once())->method('setForceNoAnsiColorSupport')->with($with);
    }

    /**
     * @param TTY&MockObject $file
     * @param array $consecutive_args
     */
    private function expectsWriteToBeCalledWithConsecutive($file, array $consecutive_args)
    {
        $method = $file
            ->expects(self::exactly(count($consecutive_args)))
            ->method('write');
        call_user_func_array([$method, 'withConsecutive'], $consecutive_args);
    }

}
