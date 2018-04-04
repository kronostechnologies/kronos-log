<?php

namespace Kronos\Tests\Log\Writer;

use Kronos\Log\Writer\TriggerError;
use Psr\Log\LogLevel;

class TriggerErrorTest extends \PHPUnit_Framework_TestCase
{
    const ANY_LOG_LEVEL = LogLevel::INFO;
    const LOG_MESSAGE = 'log message';
    const MESSAGE_WITH_INTERPOLATION = 'message with {interpolation}';
    const INTERPOLATION_KEY = 'interpolation';
    const INTERPOLATED_VALUE = 'interpolated value';
    const INVALID_LOG_LEVEL = 'invalid log level';

    /**
     * @var TriggerError
     */
    private $writer;

    public function setUp()
    {
        $this->writer = new TriggerError();
    }

    public function test_AnyLogLevel_log_ShouldTriggerErrorWithMessage()
    {
        $triggeredErrors = [];
        $previousErrorHandler = $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(self::ANY_LOG_LEVEL, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(self::LOG_MESSAGE, $triggeredErrors[0]['errstr']);
        }
        finally {
            set_error_handler($previousErrorHandler);
        }
    }

    public function test_MessageWithInterpolation_log_ShouldInterpolateMessage()
    {
        $triggeredErrors = [];
        $previousErrorHandler = $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(self::ANY_LOG_LEVEL, self::MESSAGE_WITH_INTERPOLATION,
                [self::INTERPOLATION_KEY => self::INTERPOLATED_VALUE]);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(str_replace('{'.self::INTERPOLATION_KEY.'}', self::INTERPOLATED_VALUE, self::MESSAGE_WITH_INTERPOLATION), $triggeredErrors[0]['errstr']);
        }
        finally {
            set_error_handler($previousErrorHandler);
        }
    }

    public function test_Emergency_log_ShouldTriggerUserWarning() {
        $triggeredErrors = [];
        $previousErrorHandler = $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::EMERGENCY, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_WARNING, $triggeredErrors[0]['errno']);
        }
        finally {
            set_error_handler($previousErrorHandler);
        }
    }

    public function test_Alert_log_ShouldTriggerUserWarning() {
        $triggeredErrors = [];
        $previousErrorHandler = $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::ALERT, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_WARNING, $triggeredErrors[0]['errno']);
        }
        finally {
            set_error_handler($previousErrorHandler);
        }
    }

    public function test_Critical_log_ShouldTriggerUserWarning() {
        $triggeredErrors = [];
        $previousErrorHandler = $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::CRITICAL, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_WARNING, $triggeredErrors[0]['errno']);
        }
        finally {
            set_error_handler($previousErrorHandler);
        }
    }

    public function test_Error_log_ShouldTriggerUserWarning() {
        $triggeredErrors = [];
        $previousErrorHandler = $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::ERROR, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_WARNING, $triggeredErrors[0]['errno']);
        }
        finally {
            set_error_handler($previousErrorHandler);
        }
    }

    public function test_Warning_log_ShouldTriggerUserWarning() {
        $triggeredErrors = [];
        $previousErrorHandler = $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::WARNING, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_WARNING, $triggeredErrors[0]['errno']);
        }
        finally {
            set_error_handler($previousErrorHandler);
        }
    }

    public function test_Notice_log_ShouldTriggerUserNotice() {
        $triggeredErrors = [];
        $previousErrorHandler = $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::NOTICE, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_NOTICE, $triggeredErrors[0]['errno']);
        }
        finally {
            set_error_handler($previousErrorHandler);
        }
    }

    public function test_Info_log_ShouldTriggerUserNotice() {
        $triggeredErrors = [];
        $previousErrorHandler = $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::INFO, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_NOTICE, $triggeredErrors[0]['errno']);
        }
        finally {
            set_error_handler($previousErrorHandler);
        }
    }

    public function test_Debug_log_ShouldTriggerUserNotice() {
        $triggeredErrors = [];
        $previousErrorHandler = $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(LogLevel::DEBUG, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_NOTICE, $triggeredErrors[0]['errno']);
        }
        finally {
            set_error_handler($previousErrorHandler);
        }
    }

    public function test_InvalidLogLevel_log_ShouldTriggerUserWarning() {
        $triggeredErrors = [];
        $previousErrorHandler = $this->setUpErrorHandler($triggeredErrors);

        try {
            $this->writer->log(self::INVALID_LOG_LEVEL, self::LOG_MESSAGE);

            $this->assertEquals(1, count($triggeredErrors));
            $this->assertEquals(E_USER_WARNING, $triggeredErrors[0]['errno']);
        }
        finally {
            set_error_handler($previousErrorHandler);
        }
    }

    /**
     * @return array
     */
    private function setUpErrorHandler(&$triggeredErrors)
    {
        return set_error_handler(function ($errno, $errstr) use (&$triggeredErrors) {
            $triggeredErrors[] = ['errno' => $errno, 'errstr' => $errstr];
        });
    }
}