<?php

namespace Kronos\Tests\Log\Writer;

use Kronos\Log\Adaptor\SyslogAdaptor;
use Kronos\Log\Writer\SyslogWriter;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LogLevel;

class SyslogWriterTest extends \PHPUnit\Framework\TestCase
{
    const string APPLICATION = 'application';
    const int SYSLOG_OPTION = LOG_ODELAY;
    const int SYSLOG_FACILITY = LOG_LOCAL0;

    const string ANY_LOG_LEVEL = LogLevel::NOTICE;
    const string A_MESSAGE = 'a message {key}';
    const string CONTEXT_KEY = 'key';
    const string CONTEXT_VALUE = 'value';
    const string INTERPOLATED_MESSAGE = 'a message value';
    const string INVALID_LOG_LEVEL = 'invalid log level';

    private SyslogWriter $writer;

    private SyslogAdaptor $syslogAdaptor;

    public function setUp(): void
    {
        $this->syslogAdaptor = $this->createMock(SyslogAdaptor::class);

        $this->writer = new SyslogWriter(
            self::APPLICATION,
            self::SYSLOG_OPTION,
            self::SYSLOG_FACILITY,
            $this->syslogAdaptor,
        );
    }

    #[Test]
    public function writer_Log_ShouldCallAdaptorLogWithApplicationOptionAndFacility()
    {
        $this->expectsAdaptorLogToBeCalledWith(
            self::APPLICATION,
            self::SYSLOG_OPTION,
            self::SYSLOG_FACILITY,
            $this->anything(),
            $this->anything()
        );

        $this->writer->log(self::ANY_LOG_LEVEL, self::A_MESSAGE);
    }

    #[Test]
    public function writer_Log_ShouldInterpolateContextAndMessageSendToAdaptor()
    {
        $this->expectsAdaptorLogToBeCalledWith(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything(),
            self::INTERPOLATED_MESSAGE
        );

        $this->writer->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
    }

    #[Test]
    public function writer_LogEMERGENCY_ShouldTranslateTo_LOG_EMERG()
    {
        $this->expectsAdaptorLogToBeCalledWithPriority(LOG_EMERG);

        $this->writer->log(LogLevel::EMERGENCY, self::A_MESSAGE);
    }

    #[Test]
    public function writer_LogALERT_ShouldTranslateTo_LOG_ALERT()
    {
        $this->expectsAdaptorLogToBeCalledWithPriority(LOG_ALERT);

        $this->writer->log(LogLevel::ALERT, self::A_MESSAGE);
    }

    #[Test]
    public function writer_LogCRITICAL_ShouldTranslateTo_LOG_CRIT()
    {
        $this->expectsAdaptorLogToBeCalledWithPriority(LOG_CRIT);

        $this->writer->log(LogLevel::CRITICAL, self::A_MESSAGE);
    }

    #[Test]
    public function writer_LogERROR_ShouldTranslateTo_LOG_ERR()
    {
        $this->expectsAdaptorLogToBeCalledWithPriority(LOG_ERR);

        $this->writer->log(LogLevel::ERROR, self::A_MESSAGE);
    }

    #[Test]
    public function writer_LogWARNING_ShouldTranslateTo_LOG_WARNING()
    {
        $this->expectsAdaptorLogToBeCalledWithPriority(LOG_WARNING);

        $this->writer->log(LogLevel::WARNING, self::A_MESSAGE);
    }

    #[Test]
    public function writer_LogNOTICE_ShouldTranslateTo_LOG_NOTICE()
    {
        $this->expectsAdaptorLogToBeCalledWithPriority(LOG_NOTICE);

        $this->writer->log(LogLevel::NOTICE, self::A_MESSAGE);
    }

    #[Test]
    public function writer_LogINFO_ShouldTranslateTo_LOG_INFO()
    {
        $this->expectsAdaptorLogToBeCalledWithPriority(LOG_INFO);

        $this->writer->log(LogLevel::INFO, self::A_MESSAGE);
    }

    #[Test]
    public function writer_LogDEBUG_ShouldTranslateTo_LOG_DEBUG()
    {
        $this->expectsAdaptorLogToBeCalledWithPriority(LOG_DEBUG);

        $this->writer->log(LogLevel::DEBUG, self::A_MESSAGE);
    }

    #[Test]
    public function writer_LogInvalidLevel_ShouldThrowAnInvalidLogLevelException()
    {
        $this->expectException(\Kronos\Log\Exception\InvalidLogLevel::class);

        $this->writer->log(self::INVALID_LOG_LEVEL, self::A_MESSAGE);
    }

    private function expectsAdaptorLogToBeCalledWith($ident, $option, $facility, $priority, $message)
    {
        $this->syslogAdaptor
            ->expects($this->once())
            ->method('log')
            ->with(
                $ident,
                $option,
                $facility,
                $priority,
                $message
            );
    }

    private function expectsAdaptorLogToBeCalledWithPriority($priority)
    {
        $this->expectsAdaptorLogToBeCalledWith(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $priority,
            $this->anything()
        );
    }
}
