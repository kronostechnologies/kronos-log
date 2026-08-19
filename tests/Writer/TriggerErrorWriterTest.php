<?php

namespace Kronos\Tests\Log\Writer;

use Kronos\Log\Writer\TriggerErrorWriter;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LogLevel;

class TriggerErrorWriterTest extends \PHPUnit\Framework\TestCase
{
    const string ANY_LOG_LEVEL = LogLevel::INFO;
    const string LOG_MESSAGE = 'log message';
    const string MESSAGE_WITH_INTERPOLATION = 'message with {interpolation}';
    const string INTERPOLATION_KEY = 'interpolation';
    const string INTERPOLATED_VALUE = 'interpolated value';
    const string INVALID_LOG_LEVEL = 'invalid log level';

    private TriggerErrorWriter $writer;

    public function setUp(): void
    {
        $this->writer = new TriggerErrorWriter();
    }

    #[Test]
    public function anyLogLevel_log_ShouldTriggerErrorWithMessage()
    {
        $triggeredErrors = [];
        $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(self::ANY_LOG_LEVEL, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(self::LOG_MESSAGE, $triggeredErrors[0]['errstr']);
        } finally {
            restore_error_handler();
        }
    }

    #[Test]
    public function messageWithInterpolation_log_ShouldInterpolateMessage()
    {
        $triggeredErrors = [];
        $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE_WITH_INTERPOLATION,
                [self::INTERPOLATION_KEY => self::INTERPOLATED_VALUE]);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(str_replace('{' . self::INTERPOLATION_KEY . '}', self::INTERPOLATED_VALUE,
                self::MESSAGE_WITH_INTERPOLATION), $triggeredErrors[0]['errstr']);
        } finally {
            restore_error_handler();
        }
    }

    #[Test]
    public function emergency_log_ShouldTriggerUserWarning()
    {
        $triggeredErrors = [];
        $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::EMERGENCY, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_WARNING, $triggeredErrors[0]['errno']);
        } finally {
            restore_error_handler();
        }
    }

    #[Test]
    public function alert_log_ShouldTriggerUserWarning()
    {
        $triggeredErrors = [];
        $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::ALERT, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_WARNING, $triggeredErrors[0]['errno']);
        } finally {
            restore_error_handler();
        }
    }

    #[Test]
    public function critical_log_ShouldTriggerUserWarning()
    {
        $triggeredErrors = [];
        $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::CRITICAL, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_WARNING, $triggeredErrors[0]['errno']);
        } finally {
            restore_error_handler();
        }
    }

    #[Test]
    public function error_log_ShouldTriggerUserWarning()
    {
        $triggeredErrors = [];
        $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::ERROR, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_WARNING, $triggeredErrors[0]['errno']);
        } finally {
            restore_error_handler();
        }
    }

    #[Test]
    public function warning_log_ShouldTriggerUserWarning()
    {
        $triggeredErrors = [];
        $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::WARNING, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_WARNING, $triggeredErrors[0]['errno']);
        } finally {
            restore_error_handler();
        }
    }

    #[Test]
    public function notice_log_ShouldTriggerUserNotice()
    {
        $triggeredErrors = [];
        $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::NOTICE, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_NOTICE, $triggeredErrors[0]['errno']);
        } finally {
            restore_error_handler();
        }
    }

    #[Test]
    public function info_log_ShouldTriggerUserNotice()
    {
        $triggeredErrors = [];
        $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::INFO, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_NOTICE, $triggeredErrors[0]['errno']);
        } finally {
            restore_error_handler();
        }
    }

    #[Test]
    public function debug_log_ShouldTriggerUserNotice()
    {
        $triggeredErrors = [];
        $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::DEBUG, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_NOTICE, $triggeredErrors[0]['errno']);
        } finally {
            restore_error_handler();
        }
    }

    #[Test]
    public function invalidLogLevel_log_ShouldTriggerUserWarning()
    {
        $triggeredErrors = [];
        $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(self::INVALID_LOG_LEVEL, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_WARNING, $triggeredErrors[0]['errno']);
        } finally {
            restore_error_handler();
        }
    }

    private function setUpErrorHandler(&$triggeredErrors): void
    {
        set_error_handler(function ($errno, $errstr) use (&$triggeredErrors) {
            $triggeredErrors[] = ['errno' => $errno, 'errstr' => $errstr];
        });
    }
}
