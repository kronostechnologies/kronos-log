<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\SentryStrategy;
use Kronos\Log\Exception\InvalidSetting;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\WriterFactory;
use Kronos\Log\Writer\SentryWriter as SentryWriter;
use PHPUnit\Framework\MockObject\MockObject;
use Sentry\ClientInterface;

class SentryStrategyTest extends \PHPUnit\Framework\TestCase
{
    const string MIN_LEVEL = 'debug';
    const string MAX_LEVEL = 'emergency';
    const string SENTRY_KEY = 'key';
    const string SENTRY_PROJECT_ID = 'project_id';
    const array SENTRY_OPTIONS = ['sentry' => 'options'];

    private SentryStrategy $strategy;
    private WriterFactory&MockObject $factory;
    private SentryWriter & MockObject $writer;
    private ClientInterface & MockObject $sentryClient;

    public function setUp(): void
    {
        $this->writer = $this->createMock(SentryWriter::class);
        $this->factory = $this->createMock(WriterFactory::class);
        $this->factory->method('createSentryWriter')->willReturn($this->writer);

        $this->sentryClient = $this->createMock(ClientInterface::class);

        $this->strategy = new SentryStrategy($this->factory);
    }

    public function test_SentryClient_buildFromArray_ShouldCreateSentryWriter()
    {
        $this->factory
            ->expects(self::once())
            ->method('createSentryWriter')
            ->with($this->sentryClient);
        $settings = [SentryStrategy::CLIENT => $this->sentryClient];

        $this->strategy->buildFromArray($settings);
    }

    public function test_SentryClientConfiguration_buildFromArray_ShouldCreateSentryWriterAndSentryClient()
    {
        $this->factory
            ->expects(self::once())
            ->method('createSentryWriterAndSentryClient')
            ->with(self::SENTRY_KEY, self::SENTRY_PROJECT_ID, self::SENTRY_OPTIONS)
            ->willReturn($this->writer);
        $settings = [
            SentryStrategy::KEY => self::SENTRY_KEY,
            SentryStrategy::PROJECT_ID => self::SENTRY_PROJECT_ID,
            SentryStrategy::OPTIONS => self::SENTRY_OPTIONS
        ];

        $this->strategy->buildFromArray($settings);
    }

    public function test_MinLevel_buildFromArray_ShouldSetMinLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMinLevel')
            ->with(self::MIN_LEVEL);
        $settings = [
            SentryStrategy::CLIENT => $this->sentryClient,
            SentryStrategy::MIN_LEVEL => self::MIN_LEVEL
        ];

        $this->strategy->buildFromArray($settings);
    }

    public function test_MaxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMaxLevel')
            ->with(self::MAX_LEVEL);
        $settings = [
            SentryStrategy::CLIENT => $this->sentryClient,
            SentryStrategy::MAX_LEVEL => self::MAX_LEVEL
        ];

        $this->strategy->buildFromArray($settings);
    }

    public function test_buildFromArray_ShouldReturnWriter()
    {
        $settings = [SentryStrategy::CLIENT => $this->sentryClient];

        $actualWriter = $this->strategy->buildFromArray($settings);

        $this->assertSame($this->writer, $actualWriter);
    }

    public function test_ClientSettingNotSentryClient_buildFromArray_ShouldThrowInvalidSettingException()
    {
        $notSentryClient = new \stdClass();
        $this->expectException(InvalidSetting::class);
        $this->expectExceptionMessage(SentryStrategy::CLIENT . ' setting must be an instance of Sentry Client, instance of ' . get_class($notSentryClient) . ' given');
        $settings = [SentryStrategy::CLIENT => $notSentryClient];
        $this->factory
            ->expects(self::never())
            ->method('createSentryWriter');

        $this->strategy->buildFromArray($settings);
    }

    public function test_MissingSentryOption_buildFromArray_ShouldCreateWriterAndSentryClientWithEmptyArrayOptions()
    {
        $this->factory
            ->expects(self::once())
            ->method('createSentryWriterAndSentryClient')
            ->with(self::SENTRY_KEY, self::SENTRY_PROJECT_ID, [])
            ->willReturn($this->writer);
        $settings = [
            SentryStrategy::KEY => self::SENTRY_KEY,
            SentryStrategy::PROJECT_ID => self::SENTRY_PROJECT_ID
        ];

        $this->strategy->buildFromArray($settings);
    }

    public function test_MissingClientAndKeySetting_buildFromArray_ShouldThrowRequiredSettingException()
    {
        $this->expectException(RequiredSetting::class);
        $this->expectExceptionMessage(SentryStrategy::CLIENT . ' setting or ' . SentryStrategy::KEY . ' setting must given');
        $this->factory
            ->expects(self::never())
            ->method('createSentryWriterAndSentryClient');
        $settings = [
            SentryStrategy::PROJECT_ID => self::SENTRY_PROJECT_ID
        ];

        $this->strategy->buildFromArray($settings);
    }

    public function test_KeySettingAndNoProjectId_buildFromArray_ShouldThrowRequiredSettingException()
    {
        $this->expectException(RequiredSetting::class);
        $this->expectExceptionMessage(SentryStrategy::PROJECT_ID . ' setting is required with ' . SentryStrategy::KEY);
        $this->factory
            ->expects(self::never())
            ->method('createSentryWriterAndSentryClient');
        $settings = [
            SentryStrategy::KEY => self::SENTRY_KEY,
        ];

        $this->strategy->buildFromArray($settings);
    }
}
