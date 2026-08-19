<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\MemoryStrategy;
use Kronos\Log\Factory\WriterFactory;
use Kronos\Log\Writer\MemoryWriter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class MemoryStrategyTest extends \PHPUnit\Framework\TestCase
{
    const string MIN_LEVEL = 'debug';
    const string MAX_LEVEL = 'emergency';

    private MemoryStrategy $strategy;
    private WriterFactory & MockObject $factory;
    private MemoryWriter & MockObject $writer;

    public function setUp(): void
    {
        $this->writer = $this->createMock(MemoryWriter::class);
        $this->factory = $this->createMock(WriterFactory::class);
        $this->factory->method('createMemoryWriter')->willReturn($this->writer);

        $this->strategy = new MemoryStrategy($this->factory);
    }

    #[Test]
    public function buildFromArray_ShouldCreateMemoryWriter()
    {
        $this->factory
            ->expects(self::once())
            ->method('createMemoryWriter');

        $this->strategy->buildFromArray([]);
    }

    #[Test]
    public function minLevel_buildFromArray_ShouldSetMinLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMinLevel')
            ->with(self::MIN_LEVEL);

        $this->strategy->buildFromArray([MemoryStrategy::MIN_LEVEL => self::MIN_LEVEL]);
    }

    #[Test]
    public function maxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMaxLevel')
            ->with(self::MAX_LEVEL);

        $this->strategy->buildFromArray([MemoryStrategy::MAX_LEVEL => self::MAX_LEVEL]);
    }

    #[Test]
    public function buildFromArray_ShouldReturnWriter()
    {
        $actualWriter = $this->strategy->buildFromArray([]);

        $this->assertSame($this->writer, $actualWriter);
    }
}
