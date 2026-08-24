<?php

namespace Kronos\Tests\Log\Factory\Writer;

use Kronos\Log\Exception\InvalidSetting;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\SentryFactory;
use Kronos\Log\Factory\Writer\SentryWriterFactory;
use Kronos\Log\Writer\SentryWriter as SentryWriter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Sentry\ClientInterface;

class SentryWriterFactoryTest extends \PHPUnit\Framework\TestCase
{
    const string MIN_LEVEL = 'debug';
    const string MAX_LEVEL = 'emergency';
    const string SENTRY_KEY = 'key';
    const string SENTRY_PROJECT_ID = 'project_id';
    const array SENTRY_OPTIONS = ['sentry' => 'options'];

    private SentryWriterFactory $factory;
    private SentryFactory&MockObject $sentryFactory;
    private ClientInterface & MockObject $sentryClient;

    public function setUp(): void
    {
        $this->sentryClient = $this->createMock(ClientInterface::class);
        $this->sentryFactory = $this->createMock(SentryFactory::class);
        $this->sentryFactory->method('createClient')->willReturn($this->sentryClient);

        $this->factory = new SentryWriterFactory($this->sentryFactory);
    }

    #[Test]
    public function client_create_ShouldCreateSentryWriterWithClient()
    {
        $writer = $this->factory->create($this->sentryClient);

        $this->assertInstanceOf(SentryWriter::class, $writer);
        $this->assertSame($this->sentryClient, $this->getPrivateProperty($writer, 'sentryClient'));
    }

    #[Test]
    public function key_createSentryWriterAndSentryClient_ShouldCreateClientWithKeyProjectIdAndOptions()
    {
        $this->sentryFactory
            ->expects(self::once())
            ->method('createClient')
            ->with(self::SENTRY_KEY, self::SENTRY_PROJECT_ID, self::SENTRY_OPTIONS);

        $this->factory->createSentryWriterAndSentryClient(self::SENTRY_KEY, self::SENTRY_PROJECT_ID, self::SENTRY_OPTIONS);
    }

    #[Test]
    public function createSentryWriterAndSentryClient_ShouldReturnSentryWriterWithCreatedClient()
    {
        $writer = $this->factory->createSentryWriterAndSentryClient(self::SENTRY_KEY, self::SENTRY_PROJECT_ID);

        $this->assertInstanceOf(SentryWriter::class, $writer);
        $this->assertSame($this->sentryClient, $this->getPrivateProperty($writer, 'sentryClient'));
    }

    #[Test]
    public function sentryClient_buildFromArray_ShouldCreateSentryWriter()
    {
        $settings = [SentryWriterFactory::CLIENT => $this->sentryClient];

        $writer = $this->factory->createFromArray($settings);

        $this->assertInstanceOf(SentryWriter::class, $writer);
        $this->assertSame($this->sentryClient, $this->getPrivateProperty($writer, 'sentryClient'));
    }

    #[Test]
    public function sentryClientConfiguration_buildFromArray_ShouldCreateSentryWriterAndSentryClient()
    {
        $this->sentryFactory
            ->expects(self::once())
            ->method('createClient')
            ->with(self::SENTRY_KEY, self::SENTRY_PROJECT_ID, self::SENTRY_OPTIONS)
            ->willReturn($this->sentryClient);
        $settings = [
            SentryWriterFactory::KEY => self::SENTRY_KEY,
            SentryWriterFactory::PROJECT_ID => self::SENTRY_PROJECT_ID,
            SentryWriterFactory::OPTIONS => self::SENTRY_OPTIONS
        ];

        $writer = $this->factory->createFromArray($settings);

        $this->assertSame($this->sentryClient, $this->getPrivateProperty($writer, 'sentryClient'));
    }

    #[Test]
    public function minLevel_buildFromArray_ShouldSetMinLevel()
    {
        $settings = [
            SentryWriterFactory::CLIENT => $this->sentryClient,
            SentryWriterFactory::MIN_LEVEL => self::MIN_LEVEL
        ];

        $writer = $this->factory->createFromArray($settings);

        $this->assertSame(self::MIN_LEVEL, $this->getPrivateProperty($writer, 'min_level'));
    }

    #[Test]
    public function maxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $settings = [
            SentryWriterFactory::CLIENT => $this->sentryClient,
            SentryWriterFactory::MAX_LEVEL => self::MAX_LEVEL
        ];

        $writer = $this->factory->createFromArray($settings);

        $this->assertSame(self::MAX_LEVEL, $this->getPrivateProperty($writer, 'max_level'));
    }

    #[Test]
    public function buildFromArray_ShouldReturnWriter()
    {
        $settings = [SentryWriterFactory::CLIENT => $this->sentryClient];

        $actualWriter = $this->factory->createFromArray($settings);

        $this->assertInstanceOf(SentryWriter::class, $actualWriter);
    }

    #[Test]
    public function clientSettingNotSentryClient_buildFromArray_ShouldThrowInvalidSettingException()
    {
        $notSentryClient = new \stdClass();
        $this->expectException(InvalidSetting::class);
        $this->expectExceptionMessage(SentryWriterFactory::CLIENT . ' setting must be an instance of Sentry Client, instance of ' . get_class($notSentryClient) . ' given');
        $settings = [SentryWriterFactory::CLIENT => $notSentryClient];
        $this->sentryFactory
            ->expects(self::never())
            ->method('createClient');

        $this->factory->createFromArray($settings);
    }

    #[Test]
    public function missingSentryOption_buildFromArray_ShouldCreateWriterAndSentryClientWithEmptyArrayOptions()
    {
        $this->sentryFactory
            ->expects(self::once())
            ->method('createClient')
            ->with(self::SENTRY_KEY, self::SENTRY_PROJECT_ID, []);
        $settings = [
            SentryWriterFactory::KEY => self::SENTRY_KEY,
            SentryWriterFactory::PROJECT_ID => self::SENTRY_PROJECT_ID
        ];

        $this->factory->createFromArray($settings);
    }

    #[Test]
    public function missingClientAndKeySetting_buildFromArray_ShouldThrowRequiredSettingException()
    {
        $this->expectException(RequiredSetting::class);
        $this->expectExceptionMessage(SentryWriterFactory::CLIENT . ' setting or ' . SentryWriterFactory::KEY . ' setting must given');
        $this->sentryFactory
            ->expects(self::never())
            ->method('createClient');
        $settings = [
            SentryWriterFactory::PROJECT_ID => self::SENTRY_PROJECT_ID
        ];

        $this->factory->createFromArray($settings);
    }

    #[Test]
    public function keySettingAndNoProjectId_buildFromArray_ShouldThrowRequiredSettingException()
    {
        $this->expectException(RequiredSetting::class);
        $this->expectExceptionMessage(SentryWriterFactory::PROJECT_ID . ' setting is required with ' . SentryWriterFactory::KEY);
        $this->sentryFactory
            ->expects(self::never())
            ->method('createClient');
        $settings = [
            SentryWriterFactory::KEY => self::SENTRY_KEY,
        ];

        $this->factory->createFromArray($settings);
    }

    private function getPrivateProperty(object $object, string $property): mixed
    {
        $reflectionProperty = new \ReflectionProperty($object, $property);

        return $reflectionProperty->getValue($object);
    }
}
