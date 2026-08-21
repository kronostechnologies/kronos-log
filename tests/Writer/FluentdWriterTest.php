<?php

namespace Kronos\Tests\Log\Writer;

use Fluent\Logger\FluentLogger;
use Kronos\Log\Factory\FluentdFactory;
use Kronos\Log\Writer\FluentdWriter;
use Kronos\Log\Factory\Fluentd\FluentBitJsonPacker;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

class FluentdWriterTest extends \PHPUnit\Framework\TestCase
{
    private FluentdFactory&MockObject $factory;
    private FluentLogger&MockObject $logger;
    private FluentdWriter $writer;

    public function setUp(): void
    {
        $this->factory = $this->getMockBuilder(FluentdFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(FluentLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory->method('createFluentLogger')->willReturn($this->logger);
    }

    #[Test]
    public function uninitialized_log_CreatesLoggerWithHostname()
    {
        $givenHostname = "localhost";
        $this->writer = new FluentdWriter($givenHostname, 24224, "test", null, false, $this->factory);

        $this->factory->expects($this->once())->method('createFluentLogger')->with($givenHostname, $this->anything(),
            $this->anything(), $this->anything());

        $this->writer->log(LogLevel::INFO, "test");
    }

    #[Test]
    public function uninitialized_log_CreatesLoggerWithPort()
    {
        $givenPort = 24224;
        $this->writer = new FluentdWriter("localhost", $givenPort, "test", null, false, $this->factory);

        $this->factory->expects($this->once())->method('createFluentLogger')->with($this->anything(), $givenPort);

        $this->writer->log(LogLevel::INFO, "test");
    }

    #[Test]
    public function uninitialized_log_CreatesLoggerWithFluentBitPacker()
    {
        $this->writer = new FluentdWriter("localhost", 24224, "test", null, false, $this->factory, null, true);

        $this->factory->expects($this->once())->method('createFluentLogger')->with($this->anything(), $this->anything(),
            $this->anything(), $this->isInstanceOf(FluentBitJsonPacker::class));

        $this->writer->log(LogLevel::INFO, "test");
    }

    #[Test]
    public function uninitialized_logTwice_CreatesLoggerOnlyOnce()
    {
        $this->writer = new FluentdWriter("localhost", 24224, "test", null, false, $this->factory);

        $this->factory->expects($this->once())->method('createFluentLogger');

        $this->writer->log(LogLevel::INFO, "test");
        $this->writer->log(LogLevel::INFO, "second entry");
    }

    #[Test]
    public function log_PassesTag()
    {
        $givenTag = "test";
        $this->writer = new FluentdWriter("localhost", 24224, $givenTag, null, false, $this->factory);

        $this->logger->expects($this->once())->method('post')->with($givenTag, $this->anything());

        $this->writer->log(LogLevel::INFO, "test");
    }

    #[Test]
    public function log_MessageSetInContext()
    {
        $givenMessage = "message";
        $this->writer = new FluentdWriter("localhost", 24224, "test", null, false, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenMessage) {
                return $value['message'] === $givenMessage;
            }));

        $this->writer->log(LogLevel::INFO, $givenMessage);
    }

    #[Test]
    public function log_LevelSetInContext()
    {
        $givenLevel = LogLevel::INFO;
        $this->writer = new FluentdWriter("localhost", 24224, "test", null, false, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenLevel) {
                return $value['level'] === $givenLevel;
            }));

        $this->writer->log($givenLevel, "test");
    }

    #[Test]
    public function applicationUnset_log_DoesNotContainApp()
    {
        $this->writer = new FluentdWriter("localhost", 24224, "test", null, false, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) {
                return !array_key_exists("_app", $value);
            }));

        $this->writer->log(LogLevel::INFO, "test");
    }

    #[Test]
    public function applicationSet_log_ContainsApp()
    {
        $givenApp = "testapp";
        $this->writer = new FluentdWriter("localhost", 24224, "test", $givenApp, false, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenApp) {
                return $value['_app'] === $givenApp;
            }));

        $this->writer->log(LogLevel::INFO, "test");
    }

    #[Test]
    public function doNotWrapContextInMeta_log_ContainsAppInRoot()
    {
        $givenApp = "testapp";
        $this->writer = new FluentdWriter("localhost", 24224, "test", $givenApp, false, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenApp) {
                return $value['_app'] === $givenApp;
            }));

        $this->writer->log(LogLevel::INFO, "test");
    }

    #[Test]
    public function wrapContextInMeta_log_ContainsAppInRoot()
    {
        $givenApp = "testapp";
        $this->writer = new FluentdWriter("localhost", 24224, "test", $givenApp, true, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenApp) {
                return $value['_app'] === $givenApp;
            }));

        $this->writer->log(LogLevel::INFO, "test");
    }

    #[Test]
    public function wrapContextInMeta_log_UnderlyingMetaDoesNotContainLevelOrMessage()
    {
        $givenApp = "testapp";
        $this->writer = new FluentdWriter("localhost", 24224, "test", $givenApp, true, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenApp) {
                return empty($value['meta']['level']) && empty($value['meta']['message']);
            }));

        $this->writer->log(LogLevel::INFO, "test");
    }

    #[Test]
    public function messageInContext_log_MessageOverridesGivenContext()
    {
        $givenMessage = "a message";
        $this->writer = new FluentdWriter("localhost", 24224, "test", null, false, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenMessage) {
                return $value['message'] === $givenMessage;
            }));

        $this->writer->log(LogLevel::INFO, $givenMessage, ['message' => '123']);
    }

    #[Test]
    public function customContext_log_ValueIsPosted()
    {
        $givenContextKey = "test";
        $givenContextVal = "something";
        $this->writer = new FluentdWriter("localhost", 24224, "test", null, false, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenContextKey, $givenContextVal) {
                return $value[$givenContextKey] === $givenContextVal;
            }));

        $this->writer->log(LogLevel::INFO, "test", [$givenContextKey => $givenContextVal]);
    }

    #[Test]
    public function exceptionWhenLogging_log_ReturnsFalse()
    {
        $this->logger->method('post')->willThrowException(new \Exception("Connection error"));
        $this->writer = new FluentdWriter("localhost", 24224, "test", null, false, $this->factory);

        $h = false;
        set_error_handler(static function () use (&$h) {
            $h = true;
        }, E_USER_WARNING);

        try {
            $this->writer->log(LogLevel::INFO, "Anything");
            $this->assertTrue($h);
        } finally {
            restore_error_handler();
        }
    }
}
