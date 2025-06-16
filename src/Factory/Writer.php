<?php

namespace Kronos\Log\Factory;

use Kronos\Log\Adaptor\FileFactory;
use Kronos\Log\Adaptor\Syslog as SyslogAdaptor;
use Kronos\Log\Formatter\ContextStringifier;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Writer\File;
use Kronos\Log\Writer\LogDNA;
use Kronos\Log\Writer\Sentry as SentryWriter;
use Kronos\Log\Writer\Syslog;
use Kronos\Log\Writer\Console;
use Kronos\Log\Writer\Memory;
use Kronos\Log\Writer\TriggerError;
use Psr\Log\LoggerInterface;
use Sentry;
use Sentry\ClientInterface;
use Sentry\Dsn;
use Sentry\HttpClient\HttpClientInterface;
use Sentry\Integration\IntegrationInterface;
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

    public function createFileWriter(
        ?string $filename,
        ?TraceBuilder $exceptionTraceBuilder = null,
        ?TraceBuilder $previousExceptionTraceBuilder = null
    ): File {
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

    /**
     * @param array{
     *     attach_metric_code_locations?: bool,
     *     attach_stacktrace?: bool,
     *     before_breadcrumb?: callable,
     *     before_send?: callable,
     *     before_send_check_in?: callable,
     *     before_send_log?: callable,
     *     before_send_transaction?: callable,
     *     capture_silenced_errors?: bool,
     *     context_lines?: int|null,
     *     default_integrations?: bool,
     *     dsn?: string|bool|null|Dsn,
     *     enable_logs?: bool,
     *     environment?: string|null,
     *     error_types?: int|null,
     *     http_client?: HttpClientInterface|null,
     *     http_compression?: bool,
     *     http_connect_timeout?: int|float,
     *     http_proxy?: string|null,
     *     http_proxy_authentication?: string|null,
     *     http_ssl_verify_peer?: bool,
     *     http_timeout?: int|float,
     *     ignore_exceptions?: array<class-string>,
     *     ignore_transactions?: array<string>,
     *     in_app_exclude?: array<string>,
     *     in_app_include?: array<string>,
     *     integrations?: IntegrationInterface[]|callable(IntegrationInterface[]): IntegrationInterface[],
     *     logger?: LoggerInterface|null,
     *     max_breadcrumbs?: int,
     *     max_request_body_size?: "none"|"never"|"small"|"medium"|"always",
     *     max_value_length?: int,
     *     org_id?: int|null,
     *     prefixes?: array<string>,
     *     profiles_sample_rate?: int|float|null,
     *     release?: string|null,
     *     sample_rate?: float|int,
     *     send_attempts?: int,
     *     send_default_pii?: bool,
     *     server_name?: string,
     *     spotlight?: bool,
     *     spotlight_url?: string,
     *     strict_trace_propagation?: bool,
     *     tags?: array<string>,
     *     trace_propagation_targets?: array<string>|null,
     *     traces_sample_rate?: float|int|null,
     *     traces_sampler?: callable|null,
     *     transport?: callable,
     * } $options The client options
     */
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
