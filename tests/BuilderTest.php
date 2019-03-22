<?php

namespace Kronos\Tests\Log;

use Kronos\Log\AbstractWriter;
use Kronos\Log\Builder;
use Kronos\Log\Exception\NoWriter;
use Kronos\Log\Factory\Logger as LoggerFactory;
use Kronos\Log\Logger;

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    const ANY_WRITER_TYPE = 'Console';

    const WRITER_SETTINGS = ['field' => 'value', 'otherField' => 'other value'];

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $selector;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $strategy;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $writer;

    public function setUp(): void
    {
        $this->logger = $this->createMock(Logger::class);
        $this->loggerFactory = $this->createMock(LoggerFactory::class);
        $this->loggerFactory->method('createLogger')->willReturn($this->logger);

        $this->strategy = $this->createMock(Builder\Strategy::class);
        $this->selector = $this->createMock(Builder\Strategy\Selector::class);
        $this->selector->method('getStrategyForType')->willReturn($this->strategy);

        $this->writer = $this->createMock(AbstractWriter::class);
        $this->strategy->method('buildFromArray')->willReturn($this->writer);

        $this->builder = new Builder($this->loggerFactory, $this->selector);
    }

    public function test_buildFromArray_ShouldCreateLogger()
    {
        $this->loggerFactory
            ->expects(self::once())
            ->method('createLogger');

        $this->builder->buildFromArray([['type' => self::ANY_WRITER_TYPE, 'settings' => []]]);
    }

    public function test_SettingsForWriter_buildFromArray_ShouldCreateStrategy()
    {
        $this->selector
            ->expects(self::once())
            ->method('getStrategyForType')
            ->with(self::ANY_WRITER_TYPE);

        $this->builder->buildFromArray([['type' => self::ANY_WRITER_TYPE, 'settings' => []]]);
    }

    public function test_Strategy_buildFromArray_ShouldBuildWriterFromArray()
    {
        $this->strategy
            ->expects(self::once())
            ->method('buildFromArray')
            ->with(self::WRITER_SETTINGS);

        $this->builder->buildFromArray([['type' => self::ANY_WRITER_TYPE, 'settings' => self::WRITER_SETTINGS]]);
    }

    public function test_Writer_buildFromArray_ShouldAddWriter()
    {
        $this->logger
            ->expects(self::once())
            ->method('addWriter')
            ->with($this->writer);

        $this->builder->buildFromArray([['type' => self::ANY_WRITER_TYPE, 'settings' => self::WRITER_SETTINGS]]);
    }

    public function test_AddedWriter_buildFromArray_ShouldReturnLogger()
    {
        $actualLogger = $this->builder->buildFromArray([
            [
                'type' => self::ANY_WRITER_TYPE,
                'settings' => self::WRITER_SETTINGS
            ]
        ]);

        $this->assertSame($this->logger, $actualLogger);
    }

    public function test_NoWriterSettings_buildFromArray_ShouldThrowNoWriterException()
    {
        $this->expectException(NoWriter::class);

        $this->builder->buildFromArray([]);
    }
}
