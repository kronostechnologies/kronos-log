<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\TriggerError;
use Kronos\Log\Factory\Writer;
use Kronos\Log\Writer\TriggerError AS TriggerErrorWriter;
use PHPUnit\Framework\MockObject\MockObject;

class TriggerErrorTest extends \PHPUnit\Framework\TestCase
{
    const MIN_LEVEL = 'debug';
    const MAX_LEVEL = 'emergency';
    const FILENAME_VALUE = 'filename';

    /**
     * @var TriggerError
     */
    private $strategy;

    /**
     * @var MockObject&Writer
     */
    private $factory;

    /**
     * @var MockObject&TriggerErrorWriter
     */
    private $writer;

    public function setUp(): void
    {
        $this->writer = $this->createMock(TriggerErrorWriter::class);
        $this->factory = $this->createMock(Writer::class);
        $this->factory->method('createTriggerErrorWriter')->willReturn($this->writer);

        $this->strategy = new TriggerError($this->factory);
    }

    public function test_NoSettings_buildFromArray_ShouldCreateTriggerErrorWriter()
    {
        $this->factory
            ->expects(self::once())
            ->method('createTriggerErrorWriter');

        $this->strategy->buildFromArray([]);
    }

    public function test_MinLevel_buildFromArray_ShouldSetMinLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMinLevel')
            ->with(self::MIN_LEVEL);

        $this->strategy->buildFromArray([TriggerError::MIN_LEVEL => self::MIN_LEVEL]);
    }

    public function test_MaxLevel_buildFromArray_ShouldSetMaxLevel()
    {
        $this->writer
            ->expects(self::once())
            ->method('setMaxLevel')
            ->with(self::MAX_LEVEL);

        $this->strategy->buildFromArray([TriggerError::MAX_LEVEL => self::MAX_LEVEL]);
    }

    public function test_buildFromArray_ShouldReturnWriter()
    {
        $actualWriter = $this->strategy->buildFromArray([]);

        $this->assertSame($this->writer, $actualWriter);
    }
}
