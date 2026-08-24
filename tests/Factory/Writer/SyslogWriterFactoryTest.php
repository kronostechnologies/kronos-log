<?php

namespace Kronos\Tests\Log\Factory\Writer;

use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\Writer\SyslogWriterFactory;
use Kronos\Log\Writer\SyslogWriter;
use PHPUnit\Framework\Attributes\Test;

class SyslogWriterFactoryTest extends \PHPUnit\Framework\TestCase
{
    const string MIN_LEVEL = 'debug';
    const string MAX_LEVEL = 'emergency';
    const string APPLICATION_VALUE = 'application value';

    private SyslogWriterFactory $factory;

    public function setUp(): void
    {
        $this->factory = new SyslogWriterFactory();
    }

    #[Test]
    public function application_create_ShouldCreateSyslogWriterWithApplication()
    {
        $writer = $this->factory->create(self::APPLICATION_VALUE);

        $this->assertSame(self::APPLICATION_VALUE, $this->getPrivateProperty($writer, 'application'));
    }

    #[Test]
    public function create_ShouldReturnSyslogWriter()
    {
        $writer = $this->factory->create(self::APPLICATION_VALUE);

        $this->assertInstanceOf(SyslogWriter::class, $writer);
    }

    #[Test]
    public function option_create_ShouldCreateSyslogWriterWithOption()
    {
        $writer = $this->factory->create(self::APPLICATION_VALUE, LOG_PID);

        $this->assertSame(LOG_PID, $this->getPrivateProperty($writer, 'option'));
    }

    #[Test]
    public function facility_create_ShouldCreateSyslogWriterWithFacility()
    {
        $writer = $this->factory->create(self::APPLICATION_VALUE, SyslogWriter::DEFAULT_OPTION, LOG_LOCAL6);

        $this->assertSame(LOG_LOCAL6, $this->getPrivateProperty($writer, 'facility'));
    }

    #[Test]
    public function application_buildFromArray_ShouldCreateSyslogWriterWithApplication()
    {
        $settings = $this->givenRequiredSetting();

        $writer = $this->factory->createFromArray($settings);

        $this->assertSame(self::APPLICATION_VALUE, $this->getPrivateProperty($writer, 'application'));
    }

    #[Test]
    public function option_buildFromArray_ShouldCreateSyslogWriterWithOption()
    {
        $settings = $this->givenRequiredSetting();
        $settings[SyslogWriterFactory::OPTION] = LOG_PID;

        $writer = $this->factory->createFromArray($settings);

        $this->assertSame(LOG_PID, $this->getPrivateProperty($writer, 'option'));
    }

    #[Test]
    public function facility_buildFromArray_ShouldCreateSyslogWriterWithFacility()
    {
        $settings = $this->givenRequiredSetting();
        $settings[SyslogWriterFactory::FACILITY] = LOG_LOCAL6;

        $writer = $this->factory->createFromArray($settings);

        $this->assertSame(LOG_LOCAL6, $this->getPrivateProperty($writer, 'facility'));
    }

    #[Test]
    public function minLevel_buildFromArray_ShouldSetMinLevel()
    {
        $settings = $this->givenRequiredSetting();
        $settings[SyslogWriterFactory::MIN_LEVEL] = self::MIN_LEVEL;

        $writer = $this->factory->createFromArray($settings);

        $this->assertSame(self::MIN_LEVEL, $this->getPrivateProperty($writer, 'min_level'));
    }

    #[Test]
    public function maxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $settings = $this->givenRequiredSetting();
        $settings[SyslogWriterFactory::MAX_LEVEL] = self::MAX_LEVEL;

        $writer = $this->factory->createFromArray($settings);

        $this->assertSame(self::MAX_LEVEL, $this->getPrivateProperty($writer, 'max_level'));
    }

    #[Test]
    public function buildFromArray_ShouldReturnWriter()
    {
        $settings = $this->givenRequiredSetting();

        $actualWriter = $this->factory->createFromArray($settings);

        $this->assertInstanceOf(SyslogWriter::class, $actualWriter);
    }

    #[Test]
    public function missingApplication_buildFromArray_ShouldThrowRequiredSettingException()
    {
        $this->expectException(RequiredSetting::class);
        $this->expectExceptionMessage(SyslogWriterFactory::APPLICATION . ' setting is required');

        $this->factory->createFromArray([]);
    }

    private function givenRequiredSetting(): array
    {
        return [
            SyslogWriterFactory::APPLICATION => self::APPLICATION_VALUE
        ];
    }

    private function getPrivateProperty(object $object, string $property): mixed
    {
        $reflectionProperty = new \ReflectionProperty($object, $property);

        return $reflectionProperty->getValue($object);
    }
}
