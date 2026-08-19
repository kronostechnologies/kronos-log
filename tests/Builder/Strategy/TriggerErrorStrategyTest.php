<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\TriggerErrorStrategy;
use Kronos\Log\Factory\WriterFactory;
use Kronos\Log\Writer\TriggerErrorWriter AS TriggerErrorWriter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class TriggerErrorStrategyTest extends \PHPUnit\Framework\TestCase
{
    const string MIN_LEVEL = 'debug';
    const string MAX_LEVEL = 'emergency';
    const string FILENAME_VALUE = 'filename';

    private TriggerErrorStrategy $strategy;
    private WriterFactory & MockObject $factory;
    private TriggerErrorWriter & MockObject $writer;

    public function setUp(): void
    {
        $this->writer = $this->createMock(TriggerErrorWriter::class);
        $this->factory = $this->createMock(WriterFactory::class);
        $this->factory->method('createTriggerErrorWriter')->willReturn($this->writer);

        $this->strategy = new TriggerErrorStrategy($this->factory);
    }

    #[Test]
    public function noSettings_buildFromArray_ShouldCreateTriggerErrorWriter()
    {
        $this->factory
            ->expects(self::once())
            ->method('createTriggerErrorWriter');

        $this->strategy->buildFromArray([]);
    }

    #[Test]
    public function minLevel_buildFromArray_ShouldSetMinLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMinLevel')
            ->with(self::MIN_LEVEL);

        $this->strategy->buildFromArray([TriggerErrorStrategy::MIN_LEVEL => self::MIN_LEVEL]);
    }

    #[Test]
    public function maxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMaxLevel')
            ->with(self::MAX_LEVEL);

        $this->strategy->buildFromArray([TriggerErrorStrategy::MAX_LEVEL => self::MAX_LEVEL]);
    }

    #[Test]
    public function buildFromArray_ShouldReturnWriter()
    {
        $actualWriter = $this->strategy->buildFromArray([]);

        $this->assertSame($this->writer, $actualWriter);
    }
}
