<?php

namespace Kronos\Tests\Log\Factory\Writer;

use Kronos\Log\Factory\Writer\MemoryWriterFactory;
use Kronos\Log\Writer\MemoryWriter;
use PHPUnit\Framework\Attributes\Test;

class MemoryWriterFactoryTest extends \PHPUnit\Framework\TestCase
{
    const string MIN_LEVEL = 'debug';
    const string MAX_LEVEL = 'emergency';

    private MemoryWriterFactory $factory;

    public function setUp(): void
    {
        $this->factory = new MemoryWriterFactory();
    }

    #[Test]
    public function create_ShouldReturnMemoryWriter()
    {
        $writer = $this->factory->create();

        $this->assertInstanceOf(MemoryWriter::class, $writer);
    }

    #[Test]
    public function buildFromArray_ShouldCreateMemoryWriter()
    {
        $writer = $this->factory->createFromArray([]);

        $this->assertInstanceOf(MemoryWriter::class, $writer);
    }

    #[Test]
    public function minLevel_buildFromArray_ShouldSetMinLevel()
    {
        $writer = $this->factory->createFromArray([MemoryWriterFactory::MIN_LEVEL => self::MIN_LEVEL]);

        $this->assertSame(self::MIN_LEVEL, $this->getPrivateProperty($writer, 'min_level'));
    }

    #[Test]
    public function maxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $writer = $this->factory->createFromArray([MemoryWriterFactory::MAX_LEVEL => self::MAX_LEVEL]);

        $this->assertSame(self::MAX_LEVEL, $this->getPrivateProperty($writer, 'max_level'));
    }

    #[Test]
    public function buildFromArray_ShouldReturnWriter()
    {
        $actualWriter = $this->factory->createFromArray([]);

        $this->assertInstanceOf(MemoryWriter::class, $actualWriter);
    }

    private function getPrivateProperty(object $object, string $property): mixed
    {
        $reflectionProperty = new \ReflectionProperty($object, $property);

        return $reflectionProperty->getValue($object);
    }
}
