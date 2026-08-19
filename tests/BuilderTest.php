<?php

namespace Kronos\Tests\Log;

use Kronos\Log\AbstractWriter;
use Kronos\Log\Builder;
use Kronos\Log\Exception\NoWriter;
use Kronos\Log\Factory\LoggerFactory as LoggerFactory;
use Kronos\Log\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    const ANY_WRITER_TYPE = 'Console';

    const WRITER_SETTINGS = ['field' => 'value', 'otherField' => 'other value'];

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var MockObject&LoggerFactory
     */
    private $loggerFactory;

    /**
     * @var MockObject&Builder\Strategy\StrategySelector
     */
    private $selector;

    /**
     * @var MockObject&Logger
     */
    private $logger;

    /**
     * @var MockObject&Builder\Strategy
     */
    private $strategy;

    /**
     * @var MockObject&AbstractWriter
     */
    private $writer;

    public function setUp(): void
    {
        $this->logger = $this->createMock(Logger::class);
        $this->loggerFactory = $this->createMock(LoggerFactory::class);
        $this->loggerFactory->method('createLogger')->willReturn($this->logger);

        $this->strategy = $this->createMock(Builder\Strategy::class);
        $this->selector = $this->createMock(Builder\Strategy\StrategySelector::class);
        $this->selector->method('getStrategyForType')->willReturn($this->strategy);

        $this->writer = $this->createMock(AbstractWriter::class);
        $this->strategy->method('buildFromArray')->willReturn($this->writer);

        $this->builder = new Builder($this->loggerFactory, $this->selector);
    }

    #[Test]
    public function buildFromArray_ShouldCreateLogger()
    {
        $this->loggerFactory
            ->expects(self::once())
            ->method('createLogger');

        $this->builder->buildFromArray([['type' => self::ANY_WRITER_TYPE, 'settings' => []]]);
    }

    #[Test]
    public function settingsForWriter_buildFromArray_ShouldCreateStrategy()
    {
        $this->selector
            ->expects(self::once())
            ->method('getStrategyForType')
            ->with(self::ANY_WRITER_TYPE);

        $this->builder->buildFromArray([['type' => self::ANY_WRITER_TYPE, 'settings' => []]]);
    }

    #[Test]
    public function strategy_buildFromArray_ShouldBuildWriterFromArray()
    {
        $this->strategy
            ->expects(self::once())
            ->method('buildFromArray')
            ->with(self::WRITER_SETTINGS);

        $this->builder->buildFromArray([['type' => self::ANY_WRITER_TYPE, 'settings' => self::WRITER_SETTINGS]]);
    }

    #[Test]
    public function writer_buildFromArray_ShouldAddWriter()
    {
        $this->logger
            ->expects(self::once())
            ->method('addWriter')
            ->with($this->writer);

        $this->builder->buildFromArray([['type' => self::ANY_WRITER_TYPE, 'settings' => self::WRITER_SETTINGS]]);
    }

    #[Test]
    public function addedWriter_buildFromArray_ShouldReturnLogger()
    {
        $actualLogger = $this->builder->buildFromArray([
            [
                'type' => self::ANY_WRITER_TYPE,
                'settings' => self::WRITER_SETTINGS
            ]
        ]);

        $this->assertSame($this->logger, $actualLogger);
    }

    #[Test]
    public function noWriterSettings_buildFromArray_ShouldThrowNoWriterException()
    {
        $this->expectException(NoWriter::class);

        $this->builder->buildFromArray([]);
    }
}
