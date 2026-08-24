<?php

namespace Kronos\Log\Writer;

use GuzzleHttp\Client;
use Kronos\Log\Factory\GuzzleFactory;
use Kronos\Log\Formatter\ContextStringifier;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use Kronos\Log\Traits\ExceptionTraceBuilderAwareTrait;
use Override;
use Stringable;

class LogDNAWriter extends AbstractWriter
{
    use ExceptionTraceBuilderAwareTrait;

    const string LOGDNA_URL = 'https://logs.logdna.com/';
    const string INGEST_URI = 'logs/ingest';

    const string METADATA_CONTEXT = 'context';
    const string METADATA_USER = 'user';
    const string METADATA_EXCEPTION = 'exception';

    private string $hostname;
    private ?string $application;
    private ?string $ip = null;
    private ?string $mac = null;

    private Client $guzzleClient;
    private ?TraceBuilder $exceptionTraceBuilder;
    private ?TraceBuilder $previousExceptionTraceBuilder;
    private ContextStringifier $contextStringifier;

    public function __construct(
        string $hostname,
        ?string $application,
        string $ingestionKey,
        array $guzzleOptions = [],
        ?GuzzleFactory $guzzleFactory = null,
        ?TraceBuilder $exceptionTraceBuilder = null,
        ?TraceBuilder $previousExceptionTraceBuilder = null,
        ?ContextStringifier $contextStringifier = null
    ) {
        $this->hostname = $hostname;
        $this->application = $application;
        $this->exceptionTraceBuilder = $exceptionTraceBuilder;
        $this->previousExceptionTraceBuilder = $previousExceptionTraceBuilder;
        $this->contextStringifier = $contextStringifier ?: new ContextStringifier();
        $this->createGuzzleClient($ingestionKey, $guzzleOptions, $guzzleFactory);
    }

    public function setIpAddress(?string $ip): void
    {
        $this->ip = $ip;
    }

    public function setMacAddress(?string $mac): void
    {
        $this->mac = $mac;
    }

    #[Override]
    public function log(string $level, string | Stringable $message, array $context = []): void
    {
        try {
            $metadata = $this->processMetadata($context);

            $this->guzzleClient->post($this->buildUri(), [
                'json' =>
                    [
                        'lines' => [
                            [
                                'line' => $this->interpolate($message, $context),
                                'app' => $this->application,
                                'level' => $level,
                                'meta' => [
                                    self::METADATA_CONTEXT => (isset($metadata[self::METADATA_CONTEXT])) ? $metadata[self::METADATA_CONTEXT] : []
                                ]
                            ]
                        ]
                    ]
            ]);
        } catch (\Exception $exception) {
            // A logger should never be the reason why the app crashed.
            trigger_error('An error occurred while writing with the LogDNA writer: ' . $exception->getMessage(),
                E_USER_WARNING);
        }
    }

    private function buildUri(): string
    {
        $uri = self::INGEST_URI . '?hostname=' . urlencode($this->hostname) . '&now=' . time();
        if ($this->ip) {
            $uri .= '&ip=' . urlencode($this->ip);
        }
        if ($this->mac) {
            $uri .= '&mac=' . urlencode($this->mac);
        }

        return $uri;
    }

    private function processMetadata(array $context = []): array
    {
        $exception_context = $this->replaceException($context);

        $metadata = [
            self::METADATA_CONTEXT => $this->contextStringifier->stringifyArray($exception_context)
        ];

        return $metadata;
    }

    private function createGuzzleClient($ingestionKey, $guzzleOptions, ?GuzzleFactory $guzzleFactory = null): void
    {
        $factory = $guzzleFactory ?: new GuzzleFactory();
        $baseOptions = [
            'headers' => [
                'Content-Type' => 'application/json',
                'apikey' => $ingestionKey,
                'Connection' => 'keep-alive'
            ],
            'base_uri' => self::LOGDNA_URL
        ];

        $options = $this->recursiveMerge($baseOptions, $guzzleOptions);
        $this->guzzleClient = $factory->createClient($options);
    }

    private function recursiveMerge($base, $addition)
    {
        $result = [];

        foreach ($base as $index => $value) {
            if (isset($addition[$index])) {
                if (is_array($value) && is_array($addition[$index])) {
                    $result[$index] = $this->recursiveMerge($value, $addition[$index]);
                } else {
                    $result[$index] = $value;
                }
            } else {
                $result[$index] = $value;
            }
        }

        foreach ($addition as $index => $value) {
            if (!isset($base[$index])) {
                $result[$index] = $value;
            }
        }

        return $result;
    }

    #[Override]
    public function getExceptionTraceBuilder(): ?TraceBuilder
    {
        return $this->exceptionTraceBuilder;
    }

    #[Override]
    public function getPreviousExceptionTraceBuilder(): ?TraceBuilder
    {
        return $this->previousExceptionTraceBuilder;
    }
}
