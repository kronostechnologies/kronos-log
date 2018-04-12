<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\TriggerError;
use Kronos\Log\Factory\Writer;
use Kronos\Log\Writer\TriggerError AS TriggerErrorWriter;

class TriggerErrorTest extends \PHPUnit_Framework_TestCase
{
    const MIN_LEVEL = 'debug';
    const MAX_LEVEL = 'emergency';
    const FILENAME_VALUE = 'filename';

    /**
     * @var File
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

    public function setUp()
    {
        $this->writer = $this->getMockWithoutInvokingTheOriginalConstructor(TriggerErrorWriter::class);
        $this->factory = $this->getMock(Writer::class);
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