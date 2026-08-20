<?php

namespace Kronos\Tests\Log\Factory\Writer;

use Kronos\Log\Exception\InvalidCustomWriter;
use Kronos\Log\Factory\Writer\CustomWriterFactory;
use Kronos\Log\Factory\Writer\WriterFactory;
use Kronos\Log\Writer\MemoryWriter;
use PHPUnit\Framework\Attributes\Test;

class CustomWriterFactoryTest extends \PHPUnit\Framework\TestCase
{

    #[Test]
    public function classname_getStrategyFromClassname_ShouldReturnClassnameInstance()
    {
        $customWriter = new CustomWriterFactory();

        $factory = $customWriter->getStrategyForClassname(ValidCustomWriterFactory::class);

        $this->assertInstanceOf(ValidCustomWriterFactory::class, $factory);
    }

    #[Test]
    public function unknownClass_getStrategyFromClassname_ShouldThrowThrowInvalidCustomWriterException()
    {
        $this->expectException(InvalidCustomWriter::class);
        $customWriter = new CustomWriterFactory();

        $customWriter->getStrategyForClassname('\Invalid\Strategy\Classname');
    }

    #[Test]
    public function nonBuilderStrategyClass_getStrategyFromClassname_ShouldThrowInvalidCustomWriterException()
    {
        $this->expectException(InvalidCustomWriter::class);
        $customWriter = new CustomWriterFactory();

        $customWriter->getStrategyForClassname(NonBuildStrategyClass::class);
    }
}


class ValidCustomWriterFactory implements WriterFactory
{

    public function createFromArray(array $settings): \Kronos\Log\WriterInterface
    {
        return new MemoryWriter();
    }

}

class NonBuildStrategyClass
{

}
