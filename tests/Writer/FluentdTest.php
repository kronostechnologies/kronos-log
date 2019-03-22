<?php


namespace Kronos\Tests\Log\Writer;


use Fluent\Logger\FluentLogger;
use Kronos\Log\Writer\Fluentd;
use Psr\Log\LogLevel;

class FluentdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Kronos\Log\Factory\Fluentd|\PHPUnit\Framework\MockObject\MockObject
     */
    private $factory;

    /**
     * @var FluentLogger|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var Fluentd
     */
    private $writer;

    public function setUp(): void
    {
        $this->factory = $this->getMockBuilder(\Kronos\Log\Factory\Fluentd::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(FluentLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory->method('createFluentLogger')->willReturn($this->logger);
    }

    public function test_uninitialized_log_CreatesLoggerWithHostname()
    {
        $givenHostname = "localhost";
        $this->writer = new Fluentd($givenHostname, 24224, "test", null, false, $this->factory);

        $this->factory->expects($this->once())->method('createFluentLogger')->with($givenHostname, $this->anything());

        $this->writer->log(LogLevel::INFO, "test");
    }

    public function test_uninitialized_log_CreatesLoggerWithPort()
    {
        $givenPort = 24224;
        $this->writer = new Fluentd("localhost", $givenPort, "test", null, false, $this->factory);

        $this->factory->expects($this->once())->method('createFluentLogger')->with($this->anything(), $givenPort);

        $this->writer->log(LogLevel::INFO, "test");
    }

    public function test_uninitialized_logTwice_CreatesLoggerOnlyOnce()
    {
        $this->writer = new Fluentd("localhost", 24224, "test", null, false, $this->factory);

        $this->factory->expects($this->once())->method('createFluentLogger');

        $this->writer->log(LogLevel::INFO, "test");
        $this->writer->log(LogLevel::INFO, "second entry");
    }

    public function test_log_PassesTag()
    {
        $givenTag = "test";
        $this->writer = new Fluentd("localhost", 24224, $givenTag, null, false, $this->factory);

        $this->logger->expects($this->once())->method('post')->with($givenTag, $this->anything());

        $this->writer->log(LogLevel::INFO, "test");
    }

    public function test_log_MessageSetInContext()
    {
        $givenMessage = "message";
        $this->writer = new Fluentd("localhost", 24224, "test", null, false, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenMessage) {
                return $value['message'] === $givenMessage;
            }));

        $this->writer->log(LogLevel::INFO, $givenMessage);
    }

    public function test_log_LevelSetInContext()
    {
        $givenLevel = LogLevel::INFO;
        $this->writer = new Fluentd("localhost", 24224, "test", null, false, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenLevel) {
                return $value['level'] === $givenLevel;
            }));

        $this->writer->log($givenLevel, "test");
    }

    public function test_ApplicationUnset_log_DoesNotContainApp()
    {
        $this->writer = new Fluentd("localhost", 24224, "test", null, false, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) {
                return !array_key_exists("_app", $value);
            }));

        $this->writer->log(LogLevel::INFO, "test");
    }

    public function test_ApplicationSet_log_ContainsApp()
    {
        $givenApp = "testapp";
        $this->writer = new Fluentd("localhost", 24224, "test", $givenApp, false, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenApp) {
                return $value['_app'] === $givenApp;
            }));

        $this->writer->log(LogLevel::INFO, "test");
    }

    public function test_DoNotWrapContextInMeta_log_ContainsAppInRoot()
    {
        $givenApp = "testapp";
        $this->writer = new Fluentd("localhost", 24224, "test", $givenApp, false, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenApp) {
                return $value['_app'] === $givenApp;
            }));

        $this->writer->log(LogLevel::INFO, "test");
    }

    public function test_WrapContextInMeta_log_ContainsAppInRoot()
    {
        $givenApp = "testapp";
        $this->writer = new Fluentd("localhost", 24224, "test", $givenApp, true, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenApp) {
                return $value['_app'] === $givenApp;
            }));

        $this->writer->log(LogLevel::INFO, "test");
    }

    public function test_WrapContextInMeta_log_UnderlyingMetaDoesNotContainLevelOrMessage()
    {
        $givenApp = "testapp";
        $this->writer = new Fluentd("localhost", 24224, "test", $givenApp, true, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenApp) {
                return $value['meta']['level'] === null && $value['meta']['message'] === null;
            }));

        $this->writer->log(LogLevel::INFO, "test");
    }

    public function test_MessageInContext_log_MessageOverridesGivenContext()
    {
        $givenMessage = "a message";
        $this->writer = new Fluentd("localhost", 24224, "test", null, false, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenMessage) {
                return $value['message'] === $givenMessage;
            }));

        $this->writer->log(LogLevel::INFO, $givenMessage, ['message' => '123']);
    }

    public function test_CustomContext_log_ValueIsPosted()
    {
        $givenContextKey = "test";
        $givenContextVal = "something";
        $this->writer = new Fluentd("localhost", 24224, "test", null, false, $this->factory);

        $this->logger->expects($this->once())
            ->method('post')
            ->with($this->anything(), $this->callback(function ($value) use ($givenContextKey, $givenContextVal) {
                return $value[$givenContextKey] === $givenContextVal;
            }));

        $this->writer->log(LogLevel::INFO, "test", [$givenContextKey => $givenContextVal]);
    }

    public function test_ExceptionWhenLogging_log_ReturnsFalse()
    {
        $this->logger->method('post')->willThrowException(new \Exception("Connection error"));
        $this->writer = new Fluentd("localhost", 24224, "test", null, false, $this->factory);

        $this->writer->log(LogLevel::INFO, "test");

        $h = false;
        set_error_handler(function () use (&$h) {
            $h = true;
        }, E_USER_WARNING);
        $retVal = $this->writer->log(LogLevel::INFO, "Anything");
        $this->assertTrue($h);

        $this->assertFalse($retVal);
    }
}
