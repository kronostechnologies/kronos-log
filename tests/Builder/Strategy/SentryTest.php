<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\Sentry;
use Kronos\Log\Exception\InvalidSetting;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\Writer;

class SentryTest extends \PHPUnit_Framework_TestCase
{
    const MIN_LEVEL = 'debug';
    const MAX_LEVEL = 'emergency';
    const SENTRY_KEY = 'key';
    const SENTRY_SECRET = 'secret';
    const SENTRY_PROJECT_ID = 'project_id';
    const SENTRY_OPTIONS = ['sentry' => 'options'];

    /**
     * @var Sentry
     */
    private $strategy;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $writer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $ravenClient;

    public function setUp()
    {
        $this->writer = $this->getMockWithoutInvokingTheOriginalConstructor(\Kronos\Log\Writer\Syslog::class);
        $this->factory = $this->getMock(Writer::class);
        $this->factory->method('createSentryWriter')->willReturn($this->writer);

        $this->ravenClient = $this->getMockWithoutInvokingTheOriginalConstructor(\Raven_Client::class);

        $this->strategy = new Sentry($this->factory);
    }

    public function test_RavenClient_buildFromArray_ShouldCreateSentryWriter()
    {
        $this->factory
            ->expects(self::once())
            ->method('createSentryWriter')
            ->with($this->ravenClient);
        $settings = [Sentry::CLIENT => $this->ravenClient];

        $this->strategy->buildFromArray($settings);
    }

    public function test_RavenClientConfiguration_buildFromArray_ShouldCreateSentryWriterAndRavenClient()
    {
        $this->factory
            ->expects(self::once())
            ->method('createSentryWriterAndRavenClient')
            ->with(self::SENTRY_KEY, self::SENTRY_SECRET, self::SENTRY_PROJECT_ID, self::SENTRY_OPTIONS)
            ->willReturn($this->writer);
        $settings = [
            Sentry::KEY => self::SENTRY_KEY,
            Sentry::SECRET => self::SENTRY_SECRET,
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
            Sentry::CLIENT => $this->ravenClient,
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
            Sentry::CLIENT => $this->ravenClient,
            Sentry::MAX_LEVEL => self::MAX_LEVEL
        ];

        $this->strategy->buildFromArray($settings);
    }

    public function test_buildFromArray_ShouldReturnWriter()
    {
        $settings = [Sentry::CLIENT => $this->ravenClient];

        $actualWriter = $this->strategy->buildFromArray($settings);

        $this->assertSame($this->writer, $actualWriter);
    }

    public function test_ClientSettingNotRavenClient_buildFromArray_ShouldThrowInvalidSettingException()
    {
        $notRavenClient = new \stdClass();
        $this->expectException(InvalidSetting::class);
        $this->expectExceptionMessage(Sentry::CLIENT . ' setting must be an instance of Raven_Client, instance of ' . get_class($notRavenClient) . ' given');
        $settings = [Sentry::CLIENT => $notRavenClient];
        $this->factory
            ->expects(self::never())
            ->method('createSentryWriter');

        $this->strategy->buildFromArray($settings);
    }

    public function test_MissingSentryOption_buildFromArray_ShouldCreateWriterAndRavenClientWithEmptyArrayOptions()
    {
        $this->factory
            ->expects(self::once())
            ->method('createSentryWriterAndRavenClient')
            ->with(self::SENTRY_KEY, self::SENTRY_SECRET, self::SENTRY_PROJECT_ID, [])
            ->willReturn($this->writer);
        $settings = [
            Sentry::KEY => self::SENTRY_KEY,
            Sentry::SECRET => self::SENTRY_SECRET,
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
            ->method('createSentryWriterAndRavenClient');
        $settings = [
            Sentry::SECRET => self::SENTRY_SECRET,
            Sentry::PROJECT_ID => self::SENTRY_PROJECT_ID
        ];

        $this->strategy->buildFromArray($settings);
    }

    public function test_KeySettingAndNoSecret_buildFromArray_ShouldThrowRequiredSettingException()
    {
        $this->expectException(RequiredSetting::class);
        $this->expectExceptionMessage(Sentry::SECRET . ' setting is required with ' . Sentry::KEY);
        $this->factory
            ->expects(self::never())
            ->method('createSentryWriterAndRavenClient');
        $settings = [
            Sentry::KEY => self::SENTRY_KEY,
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
            ->method('createSentryWriterAndRavenClient');
        $settings = [
            Sentry::KEY => self::SENTRY_KEY,
            Sentry::SECRET => self::SENTRY_SECRET
        ];

        $this->strategy->buildFromArray($settings);
    }
}