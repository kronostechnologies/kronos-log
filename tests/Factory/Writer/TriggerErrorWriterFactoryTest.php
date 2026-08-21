<?php

namespace Kronos\Tests\Log\Factory\Writer;

use Kronos\Log\Factory\Writer\TriggerErrorWriterFactory;
use Kronos\Log\Writer\TriggerErrorWriter as TriggerErrorWriter;
use PHPUnit\Framework\Attributes\Test;

class TriggerErrorWriterFactoryTest extends \PHPUnit\Framework\TestCase
{
    const string MIN_LEVEL = 'debug';
    const string MAX_LEVEL = 'emergency';

    private TriggerErrorWriterFactory $factory;

    public function setUp(): void
    {
        $this->factory = new TriggerErrorWriterFactory();
    }

    #[Test]
    public function create_ShouldReturnTriggerErrorWriter()
    {
        $writer = $this->factory->create();

        $this->assertInstanceOf(TriggerErrorWriter::class, $writer);
    }

    #[Test]
    public function noSettings_buildFromArray_ShouldCreateTriggerErrorWriter()
    {
        $writer = $this->factory->createFromArray([]);

        $this->assertInstanceOf(TriggerErrorWriter::class, $writer);
    }

    #[Test]
    public function minLevel_buildFromArray_ShouldSetMinLevel()
    {
        $writer = $this->factory->createFromArray([TriggerErrorWriterFactory::MIN_LEVEL => self::MIN_LEVEL]);

        $this->assertSame(self::MIN_LEVEL, $this->getPrivateProperty($writer, 'min_level'));
    }

    #[Test]
    public function maxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $writer = $this->factory->createFromArray([TriggerErrorWriterFactory::MAX_LEVEL => self::MAX_LEVEL]);

        $this->assertSame(self::MAX_LEVEL, $this->getPrivateProperty($writer, 'max_level'));
    }

    #[Test]
    public function buildFromArray_ShouldReturnWriter()
    {
        $actualWriter = $this->factory->createFromArray([]);

        $this->assertInstanceOf(TriggerErrorWriter::class, $actualWriter);
    }

    private function getPrivateProperty(object $object, string $property): mixed
    {
        $reflectionProperty = new \ReflectionProperty($object, $property);

        return $reflectionProperty->getValue($object);
    }
}
