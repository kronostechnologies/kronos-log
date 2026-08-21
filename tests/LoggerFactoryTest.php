<?php

namespace Kronos\Tests\Log;

use Kronos\Log\AbstractWriter;
use Kronos\Log\Exception\NoWriter;
use Kronos\Log\Factory\Writer\WriterFactory;
use Kronos\Log\Factory\Writer\WriterFactoryProvider;
use Kronos\Log\LoggerFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoggerFactoryTest extends TestCase
{
    private const string ANY_WRITER_TYPE = 'Console';
    private const array WRITER_SETTINGS = ['field' => 'value', 'otherField' => 'other value'];

    private LoggerFactory $builder;
    private WriterFactoryProvider & MockObject $selector;
    private WriterFactory & MockObject $writerFactory;
    private AbstractWriter & MockObject $writer;

    public function setUp(): void
    {

        $this->writerFactory = $this->createMock(WriterFactory::class);
        $this->selector = $this->createMock(WriterFactoryProvider::class);
        $this->selector->method('getWriterFactoryForType')->willReturn($this->writerFactory);

        $this->writer = $this->createMock(AbstractWriter::class);
        $this->writerFactory->method('createFromArray')->willReturn($this->writer);

        $this->builder = new LoggerFactory($this->selector);
    }

    #[Test]
    public function settingsForWriter_buildFromArray_ShouldCreateStrategy()
    {
        $this->selector
            ->expects(self::once())
            ->method('getWriterFactoryForType')
            ->with(self::ANY_WRITER_TYPE);

        $this->builder->createFromArray([['type' => self::ANY_WRITER_TYPE, 'settings' => []]]);
    }

    #[Test]
    public function writerFactory_buildFromArray_ShouldBuildWriterFromArray()
    {
        $this->writerFactory
            ->expects(self::once())
            ->method('createFromArray')
            ->with(self::WRITER_SETTINGS);

        $this->builder->createFromArray([['type' => self::ANY_WRITER_TYPE, 'settings' => self::WRITER_SETTINGS]]);
    }

    #[Test]
    public function writer_buildFromArray_ShouldAddWriter()
    {
        $logger = $this->builder->createFromArray([['type' => self::ANY_WRITER_TYPE, 'settings' => self::WRITER_SETTINGS]]);

        $this->assertContains($this->writer, $logger->getWriters());
    }

    #[Test]
    public function noWriterSettings_buildFromArray_ShouldThrowNoWriterException()
    {
        $this->expectException(NoWriter::class);

        $this->builder->createFromArray([]);
    }
}
