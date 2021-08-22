<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\Sentry;
use Kronos\Log\Exception\InvalidSetting;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\Writer;
use Kronos\Log\Writer\Sentry as SentryWriter;
use PHPUnit\Framework\MockObject\MockObject;
use Sentry\ClientInterface;

class SentryTest extends \PHPUnit\Framework\TestCase
{
    const MIN_LEVEL = 'debug';
    const MAX_LEVEL = 'emergency';
    const SENTRY_KEY = 'key';
    const SENTRY_PROJECT_ID = 'project_id';
    const SENTRY_OPTIONS = ['sentry' => 'options'];

    /**
     * @var Sentry
     */
    private $strategy;

    /**
     * @var Writer&MockObject
     */
    private $factory;

    /**
     * @var SentryWriter&MockObject
     */
    private $writer;

    /**
     * @var ClientInterface&MockObject
     */
    private $sentryClient;

    public function setUp(): void
    {
        $this->writer = $this->createMock(SentryWriter::class);
        $this->factory = $this->createMock(Writer::class);
        $this->factory->method('createSentryWriter')->willReturn($this->writer);

        $this->sentryClient = $this->createMock(ClientInterface::class);

        $this->strategy = new Sentry($this->factory);
    }

    public function test_SentryClient_buildFromArray_ShouldCreateSentryWriter()
    {
        $this->factory
            ->expects(self::once())
            ->method('createSentryWriter')
            ->with($this->sentryClient);
        $settings = [Sentry::CLIENT => $this->sentryClient];

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
            Sentry::KEY => self::SENTRY_KEY,
            Sentry::PROJECT_ID => self::SENTRY_PROJECT_ID,
            Sentry::OPTIONS => self::SENTRY_OPTIONS
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
            Sentry::CLIENT => $this->sentryClient,
            Sentry::MIN_LEVEL => self::MIN_LEVEL
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
            Sentry::CLIENT => $this->sentryClient,
            Sentry::MAX_LEVEL => self::MAX_LEVEL
        ];

        $this->strategy->buildFromArray($settings);
    }

    public function test_buildFromArray_ShouldReturnWriter()
    {
        $settings = [Sentry::CLIENT => $this->sentryClient];

        $actualWriter = $this->strategy->buildFromArray($settings);

        $this->assertSame($this->writer, $actualWriter);
    }

    public function test_ClientSettingNotSentryClient_buildFromArray_ShouldThrowInvalidSettingException()
    {
        $notSentryClient = new \stdClass();
        $this->expectException(InvalidSetting::class);
        $this->expectExceptionMessage(Sentry::CLIENT . ' setting must be an instance of Sentry Client, instance of ' . get_class($notSentryClient) . ' given');
        $settings = [Sentry::CLIENT => $notSentryClient];
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
            Sentry::KEY => self::SENTRY_KEY,
            Sentry::PROJECT_ID => self::SENTRY_PROJECT_ID
        ];

        $this->strategy->buildFromArray($settings);
    }

    public function test_MissingClientAndKeySetting_buildFromArray_ShouldThrowRequiredSettingException()
    {
        $this->expectException(RequiredSetting::class);
        $this->expectExceptionMessage(Sentry::CLIENT . ' setting or ' . Sentry::KEY . ' setting must given');
        $this->factory
            ->expects(self::never())
            ->method('createSentryWriterAndSentryClient');
        $settings = [
            Sentry::PROJECT_ID => self::SENTRY_PROJECT_ID
        ];

        $this->strategy->buildFromArray($settings);
    }

    public function test_KeySettingAndNoProjectId_buildFromArray_ShouldThrowRequiredSettingException()
    {
        $this->expectException(RequiredSetting::class);
        $this->expectExceptionMessage(Sentry::PROJECT_ID . ' setting is required with ' . Sentry::KEY);
        $this->factory
            ->expects(self::never())
            ->method('createSentryWriterAndSentryClient');
        $settings = [
            Sentry::KEY => self::SENTRY_KEY,
        ];

        $this->strategy->buildFromArray($settings);
    }
}
