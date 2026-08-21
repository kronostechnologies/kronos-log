<?php

namespace Kronos\Log\Factory\Writer;

use Kronos\Log\Exception\InvalidSetting;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\SentryFactory;
use Kronos\Log\Writer\SentryWriter;
use Override;
use Psr\Log\LoggerInterface;
use Sentry\ClientInterface;
use Sentry\Dsn;
use Sentry\HttpClient\HttpClientInterface;
use Sentry\Integration\IntegrationInterface;
use Sentry\Transport\TransportInterface;

class SentryWriterFactory extends AbstractWriterFactory
{
    const string CLIENT = 'client';
    const string KEY = 'key';
    const string PROJECT_ID = 'projectId';
    const string OPTIONS = 'options';

    private SentryFactory $sentryFactory;

    public function __construct(?SentryFactory $sentryFactory = null)
    {
        $this->sentryFactory = $sentryFactory ?? new SentryFactory();
    }

    public function create(ClientInterface $client): SentryWriter
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
     *     dsn?: string|bool|Dsn|null,
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
     *     http_enable_curl_share_handle?: bool,
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
     *     strict_trace_continuation?: bool,
     *     tags?: array<string>,
     *     trace_propagation_targets?: array<string>|null,
     *     traces_sample_rate?: float|int|null,
     *     traces_sampler?: callable|null,
     *     transport?: TransportInterface|null,
     * } $options The client options
     */
    public function createSentryWriterAndSentryClient(
        string $key,
        string $projectId,
        array $options = []
    ): SentryWriter {
        $sentryClient = $this->sentryFactory->createClient($key, $projectId, $options);
        return new SentryWriter($sentryClient);
    }

    /**
     * @throws InvalidSetting
     * @throws RequiredSetting
     */
    #[Override]
    public function createFromArray(array $settings): SentryWriter
    {
        if (isset($settings[self::CLIENT]) && $settings[self::CLIENT]) {
            if ($settings[self::CLIENT] instanceof ClientInterface) {
                $writer = $this->create($settings[self::CLIENT]);
            } else {
                throw new InvalidSetting(self::CLIENT . ' setting must be an instance of Sentry Client, instance of ' . get_class($settings[self::CLIENT]) . ' given');
            }
        } elseif (isset($settings[self::KEY])) {
            if (!isset($settings[self::PROJECT_ID])) {
                throw new RequiredSetting(self::PROJECT_ID . ' setting is required with ' . self::KEY);
            } else {
                $writer = $this->createSentryWriterAndSentryClient(
                    $settings[self::KEY],
                    $settings[self::PROJECT_ID],
                    $this->getOptions($settings)
                );
            }
        } else {
            throw new RequiredSetting(self::CLIENT . ' setting or ' . self::KEY . ' setting must given');
        }

        $this->setCommonSettings($writer, $settings);

        return $writer;
    }

    /**
     * @param array $settings
     * @return array
     */
    private function getOptions(array $settings): array
    {
        return isset($settings[self::OPTIONS]) ? $settings[self::OPTIONS] : [];
    }
}
