<?php

use Kronos\Log\LoggerDecorator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerDecoratorTest extends TestCase
{
    /**
     * @var LoggerInterface & MockObject
     */
    private $delegate;
    /**
     * @var LoggerDecorator
     */
    private $loggerDecorator;

    protected function setUp(): void
    {
        $this->delegate = $this->createMock(LoggerInterface::class);
        $this->loggerDecorator = new LoggerDecorator($this->delegate);
    }

    public function test_shouldLogWhenMessageLevelIsHigherThanLogger(): void
    {
        $this->loggerDecorator->setLevel(LogLevel::INFO);
        $this->delegate
            ->expects($this->once())
            ->method('log')
            ->with(LogLevel::WARNING, 'a message');

        $this->loggerDecorator->log(LogLevel::WARNING, 'a message');
    }

    /**
     * @dataProvider  provideLowerLogLevels
     */
    public function test_shouldNotLogWhenLoggerLevelIsHigherThanMessage($loggerLevel, $levelOfMessage): void
    {
        $this->loggerDecorator->setLevel($loggerLevel);
        $this->delegate->expects($this->never())->method('log');

        $this->loggerDecorator->log($levelOfMessage, 'a message');
    }

    public function provideLowerLogLevels(): array
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
}
