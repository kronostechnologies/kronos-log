<?php

namespace Kronos\Tests\Log;

use Kronos\Log\Formatter\Exception\LineBuilder;

class LineBuilderTest extends \PHPUnit_Framework_TestCase
{

    const A_LINE_NB = 0;
    const A_FILE_PATH = '/path/to/file/TestClass.php';
    const A_LINE = 20;
    const A_CLASS = 'TestClass';
    const A_TYPE = '->';
    const A_FUNCTION = 'testFunction';
    const SOME_ARGS = [1, 2, ['test']];

    const EMPTY_LINE = "";
    const ARRAY_TYPE = 'Array';

    public function test_givenACompleteSetOfExceptionTraceElements_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndArguments(
    )
    {
        $line_builder = new LineBuilder();
        $line_builder->setLineNb(self::A_LINE_NB);
        $line_builder->setFile(self::A_FILE_PATH);
        $line_builder->setLine(self::A_LINE);
        $line_builder->setClass(self::A_CLASS);
        $line_builder->setType(self::A_TYPE);
        $line_builder->setFunction(self::A_FUNCTION);
        $line_builder->setArgs(self::SOME_ARGS);

        $line = $line_builder->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH . '(' . self::A_LINE . '): ' . self::A_CLASS . self::A_TYPE . self::A_FUNCTION . '(1,2,' . self::ARRAY_TYPE . ')',
            $line);
    }

    public function test_givenASetOfExceptionTraceElementWithoutArgs_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoArguments(
    )
    {
        $line_builder = new LineBuilder();
        $line_builder->setLineNb(self::A_LINE_NB);
        $line_builder->setFile(self::A_FILE_PATH);
        $line_builder->setLine(self::A_LINE);
        $line_builder->setClass(self::A_CLASS);
        $line_builder->setType(self::A_TYPE);
        $line_builder->setFunction(self::A_FUNCTION);

        $line = $line_builder->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH . '(' . self::A_LINE . '): ' . self::A_CLASS . self::A_TYPE . self::A_FUNCTION . '()',
            $line);
    }

    public function test_givenASetOfExceptionTraceElementWithoutArgsAndFunction_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoFunctionOrType(
    )
    {
        $line_builder = new LineBuilder();
        $line_builder->setLineNb(self::A_LINE_NB);
        $line_builder->setFile(self::A_FILE_PATH);
        $line_builder->setLine(self::A_LINE);
        $line_builder->setClass(self::A_CLASS);

        $line = $line_builder->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH . '(' . self::A_LINE . '): ' . self::A_CLASS,
            $line);
    }

    public function test_givenASetOfExceptionTraceElementWithLineNbAndFileAndLine_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoClass(
    )
    {
        $line_builder = new LineBuilder();
        $line_builder->setLineNb(self::A_LINE_NB);
        $line_builder->setFile(self::A_FILE_PATH);
        $line_builder->setLine(self::A_LINE);
        $line = $line_builder->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH . '(' . self::A_LINE . '): ', $line);
    }

    public function test_givenASetOfExceptionTraceElementWithLineNbAndFile_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoLineNb(
    )
    {
        $line_builder = new LineBuilder();
        $line_builder->setLineNb(self::A_LINE_NB);
        $line_builder->setFile(self::A_FILE_PATH);

        $line = $line_builder->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH, $line);
    }

    public function test_givenASetOfExceptionTraceElementWithOnlyLineNb_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoFile(
    )
    {
        $line_builder = new LineBuilder();
        $line_builder->setLineNb(self::A_LINE_NB);

        $line = $line_builder->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ', $line);
    }

    public function test_givenNoExceptionTraceElement_buildExceptionString_shouldReturnAnEmptyLine()
    {
        $line_builder = new LineBuilder();
        $line = $line_builder->buildExceptionString();

        $this->assertEquals(self::EMPTY_LINE, $line);
    }

    public function test_clearLine_buildExceptionString_shouldReturnAnEmptyLine()
    {
        $line_builder = new LineBuilder();
        $line_builder->setLineNb(self::A_LINE_NB);
        $line_builder->setFile(self::A_FILE_PATH);
        $line_builder->setLine(self::A_LINE);
        $line_builder->setClass(self::A_CLASS);
        $line_builder->setType(self::A_TYPE);
        $line_builder->setFunction(self::A_FUNCTION);
        $line_builder->setArgs(self::SOME_ARGS);
        $line_builder->buildExceptionString();
        $line_builder->clearLine();

        $line = $line_builder->buildExceptionString();

        $this->assertEquals(self::EMPTY_LINE, $line);
    }

}

class LineBuilderTestClass
{
}