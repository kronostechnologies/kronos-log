<?php

use Kronos\Log\Logger;
use Kronos\Log\LoggerDecorator;
use Kronos\Log\LoggerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Psr\Log\LogLevel;

class LoggerDecoratorTest extends TestCase
{
    /**
     * @var (PsrLoggerInterface|LoggerInterface) & MockObject
     */
    private $delegate;

    public function test_shouldLogWhenMessageLevelIsHigherThanLogger(): void
    {
        $decorator = $this->givenDecoratorForPsrLoggerInterface();
        $decorator->setLevel(LogLevel::INFO);

        $this->delegate
            ->expects($this->once())
            ->method('log')
            ->with(LogLevel::WARNING, 'a message');

        $decorator->log(LogLevel::WARNING, 'a message');
    }

    #[DataProvider('provideLowerLogLevels')]
    public function test_shouldNotLogWhenLoggerLevelIsHigherThanMessage($loggerLevel, $levelOfMessage): void
    {
        $decorator = $this->givenDecoratorForPsrLoggerInterface();
        $decorator->setLevel($loggerLevel);

        $this->delegate->expects($this->never())->method('log');

        $decorator->log($levelOfMessage, 'a message');
    }

    public function test_loggerInterface_addContext_addContextToDelegate(): void
    {
        $decorator = $this->givenDecoratorForLoggerInterface();

        $this->delegate->expects(self::once())
            ->method('addContext')
            ->with("key", "value");

        $decorator->addContext("key", "value");
    }

    public function test_psrLoggerInterface_addContext_addContextToDelegate(): void
    {
        $decorator = $this->givenDecoratorForPsrLoggerInterface();

        $decorator->addContext("key", "value");

        self::assertTrue(true, "Did not call non-existing method");
    }

    public function test_loggerInterface_addContextArray_addContextToDelegate(): void
    {
        $decorator = $this->givenDecoratorForLoggerInterface();

        $this->delegate->expects(self::once())
            ->method('addContextArray')
            ->with(["key" => "value"]);

        $decorator->addContextArray(["key" => "value"]);
    }

    public function test_psrLoggerInterface_addContextArray_addContextToDelegate(): void
    {
        $decorator = $this->givenDecoratorForPsrLoggerInterface();

        $decorator->addContextArray(["key" => "value"]);

        self::assertTrue(true, "Did not call non-existing method");
    }

    public function test_loggerInterface_exception_logErrorWithExceptionContext(): void
    {
        $decorator = $this->givenDecoratorForLoggerInterface();
        $exception = new \RuntimeException("Eception message");

        $this->delegate->expects(self::once())
            ->method('log')
            ->with(LogLevel::ERROR, "Message", [Logger::EXCEPTION_CONTEXT => $exception]);

        $decorator->exception("Message", $exception);
    }

    public static function provideLowerLogLevels(): array
    {
        return [
            [LogLevel::INFO, LogLevel::DEBUG],
            [LogLevel::NOTICE, LogLevel::INFO],
            [LogLevel::WARNING, LogLevel::NOTICE],
            [LogLevel::ERROR, LogLevel::WARNING],
            [LogLevel::CRITICAL, LogLevel::ERROR],
            [LogLevel::EMERGENCY, LogLevel::CRITICAL]
        ];
    }

    private function givenDecoratorForLoggerInterface(): LoggerDecorator
    {
        $this->delegate = $this->createMock(LoggerInterface::class);
        return new LoggerDecorator($this->delegate);
    }

    private function givenDecoratorForPsrLoggerInterface(): LoggerDecorator
    {
        $this->delegate = $this->createMock(PsrLoggerInterface::class);
        return new LoggerDecorator($this->delegate);
    }
}
