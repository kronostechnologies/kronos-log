<?php

namespace Kronos\Log\Factory;

use Kronos\Log\Adaptor\FileFactory;
use Kronos\Log\Adaptor\Syslog As SyslogAdaptor;
use Kronos\Log\Formatter\ContextStringifier;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Writer\File;
use Kronos\Log\Writer\LogDNA;
use Kronos\Log\Writer\Sentry as SentryWriter;
use Kronos\Log\Writer\Syslog;
use Kronos\Log\Writer\Console;
use Kronos\Log\Writer\Memory;
use Kronos\Log\Writer\TriggerError;
use Sentry;
use Sentry\ClientInterface;
use Sentry\SentrySdk;

class Writer
{
    /**
     * @var SyslogAdaptor|null
     */
    private $syslog_adaptor;

    /**
     * @var FileFactory|null
     */
    private $file_factory;

    /**
     * @var ContextStringifier|null
     */
    private $context_stringifier;

    /**
     * @return File
     */
    public function createFileWriter(
        string $filename,
        ?TraceBuilder $exceptionTraceBuilder = null,
        ?TraceBuilder $previousExceptionTraceBuilder = null
    ) {
        $writer = new File($filename, $this->getFileFactory(), $exceptionTraceBuilder, $previousExceptionTraceBuilder);
        $writer->setPrependDateTime();
        $writer->setPrependLogLevel();
        $writer->setContextStringifier($this->getContextStringifier());
        return $writer;
    }

    /**
     * @param $application
     * @param int $option
     * @param int $facility
     * @return Syslog
     */
    public function createSyslogWriter($application, $option = LOG_ODELAY, $facility = LOG_LOCAL0)
    {
        return new Syslog($this->getSyslogAdaptor(), $application, $option, $facility);
    }

    /**
     * @return Console
     */
    public function createConsoleWriter(
        ?TraceBuilder $exceptionTraceBuilder = null,
        ?TraceBuilder $previousExceptionTraceBuilder = null
    ) {
        $writer = new Console($this->getFileFactory(), $exceptionTraceBuilder, $previousExceptionTraceBuilder);
        $writer->setPrependDateTime();
        $writer->setPrependLogLevel();

        return $writer;
    }

    /**
     * @return Memory
     */
    public function createMemoryWriter()
    {
        return new Memory();
    }

    public function createSentryWriter(ClientInterface $client): SentryWriter
    {
        return new SentryWriter($client);
    }

    public function createSentryWriterAndSentryClient(
        string $key,
        string $projectId,
        array $options = []
    ): SentryWriter {
        $options['dsn'] = 'https://' . $key . '@sentry.io/' . $projectId;
        Sentry\init($options);
        $sentryClient = SentrySdk::getCurrentHub()->getClient();
        return new SentryWriter($sentryClient);
    }

    public function createLogDNAWriter(
        $hostname,
        $application,
        $ingestionKey,
        ?TraceBuilder $exceptionTraceBuilder = null,
        ?TraceBuilder $previousExceptionTraceBuilder = null
    ): LogDNA {
        return new LogDNA($hostname, $application, $ingestionKey, [], null,
            $exceptionTraceBuilder, $previousExceptionTraceBuilder);
    }

    /**
     * @return TriggerError
     */
    public function createTriggerErrorWriter()
    {
        return new TriggerError();
    }

    /**
     * @return FileFactory
     */
    private function getFileFactory()
    {
        if (!$this->file_factory) {
            $this->file_factory = new FileFactory();
        }

        return $this->file_factory;
    }

    /**
     * @return SyslogAdaptor
     */
    private function getSyslogAdaptor()
    {
        if (!$this->syslog_adaptor) {
            $this->syslog_adaptor = new SyslogAdaptor();
        }

        return $this->syslog_adaptor;
    }

    /**
     * @return \Kronos\Log\Formatter\ContextStringifier
     */
    public function getContextStringifier()
    {
        if (!$this->context_stringifier) {
            $this->context_stringifier = new ContextStringifier();
        }

        return $this->context_stringifier;
    }
}
