<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\CustomWriter;
use Kronos\Log\Builder\Strategy;
use Kronos\Log\Exception\InvalidCustomWriter;

class CustomWriterTest extends \PHPUnit\Framework\TestCase
{

    public function test_Classname_getStrategyFromClassname_ShouldReturnClassnameInstance()
    {
        $customWriter = new CustomWriter();

        $strategy = $customWriter->getStrategyForClassname(ValidCustomStrategy::class);

        $this->assertInstanceOf(ValidCustomStrategy::class, $strategy);
    }

    public function test_UnknownClass_getStrategyFromClassname_ShouldThrowThrowInvalidCustomWriterException()
    {
        $this->expectException(InvalidCustomWriter::class);
        $customWriter = new CustomWriter();

        $customWriter->getStrategyForClassname('\Invalid\Strategy\Classname');
    }

    public function test_NonBuilderStrategyClass_getStrategyFromClassname_ShouldThrowInvalidCustomWriterException()
    {
        $this->expectException(InvalidCustomWriter::class);
        $customWriter = new CustomWriter();

        $customWriter->getStrategyForClassname(NonBuildStrategyClass::class);
    }
}


class ValidCustomStrategy implements Strategy
{

    public function buildFromArray(array $settings)
    {

    }

}

class NonBuildStrategyClass
{

}
