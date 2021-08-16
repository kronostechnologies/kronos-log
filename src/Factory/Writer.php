<?php

namespace Kronos\Log\Factory;

use Kronos\Log\Adaptor\FileFactory;
use Kronos\Log\Adaptor\Syslog As SyslogAdaptor;
use Kronos\Log\Formatter\ContextStringifier;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Writer\File;
use Kronos\Log\Writer\LogDNA;
use Kronos\Log\Writer\Sentry;
use Kronos\Log\Writer\Syslog;
use Kronos\Log\Writer\Console;
use Kronos\Log\Writer\Memory;
use Kronos\Log\Writer\TriggerError;
use Sentry\ClientBuilder;
use Sentry\ClientInterface;
use Sentry\Options;

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
     * @param $filename
     * @param TraceBuilder|null $exceptionTraceBuilder
     * @param TraceBuilder|null $previousExceptionTraceBuilder
     * @return File
     */
    public function createFileWriter(
        $filename,
        TraceBuilder $exceptionTraceBuilder = null,
        TraceBuilder $previousExceptionTraceBuilder = null
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
     * @param TraceBuilder|null $exceptionTraceBuilder
     * @param TraceBuilder|null $previousExceptionTraceBuilder
     * @return Console
     */
    public function createConsoleWriter(
        TraceBuilder $exceptionTraceBuilder = null,
        TraceBuilder $previousExceptionTraceBuilder = null
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

    public function createSentryWriter(ClientInterface $client): Sentry
    {
        return new Sentry($client);
    }

    public function createSentryWriterAndSentryClient(
        string $key,
        string $secret,
        string $projectId,
        array $configs = []
    ): Sentry {
        $configs['dsn'] = 'https://' . $key . ':' . $secret . '@app.getsentry.com/' . $projectId;
        $options = new Options($configs);
        $clientBuilder = new ClientBuilder($options);
        $sentryClient = $clientBuilder->getClient();
        return new Sentry($sentryClient);
    }

    /**
     * @param $hostname
     * @param $application
     * @param $ingestionKey
     * @param TraceBuilder|null $exceptionTraceBuilder
     * @param TraceBuilder|null $previousExceptionTraceBuilder
     * @return LogDNA
     */
    public function createLogDNAWriter(
        $hostname,
        $application,
        $ingestionKey,
        TraceBuilder $exceptionTraceBuilder = null,
        TraceBuilder $previousExceptionTraceBuilder = null
    ) {
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
