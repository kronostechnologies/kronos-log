<?php

namespace Kronos\Tests\Log\Formatter\Exception;

use Kronos\Log\Formatter\Exception\LineAssembler;

class LineAssemblerTest extends \PHPUnit\Framework\TestCase
{

    const A_LINE_NB = 0;
    const A_FILE_PATH = '/path/to/file/TestClass.php';
    const FILE_WITHOUT_EXTENSION = '/path/to/file/TestClass';
    const EXTENSION = '.php';
    const A_LINE = 20;
    const A_CLASS = 'TestClass';
    const A_TYPE = '->';
    const A_FUNCTION = 'testFunction';
    const SOME_ARGS = [1, 2, ['test']];

    const EMPTY_LINE = "";
    const ARRAY_TYPE = 'Array';
    const BASE_PATH = "/base/path/";

    public function test_givenACompleteSetOfExceptionTraceElements_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndArguments(
    )
    {
        $lineAssembler = new LineAssembler();
        $lineAssembler->setLineNb(self::A_LINE_NB);
        $lineAssembler->setFile(self::A_FILE_PATH);
        $lineAssembler->setLine(self::A_LINE);
        $lineAssembler->setClass(self::A_CLASS);
        $lineAssembler->setType(self::A_TYPE);
        $lineAssembler->setFunction(self::A_FUNCTION);
        $lineAssembler->setArgs(self::SOME_ARGS);

        $line = $lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH . '(' . self::A_LINE . '): ' . self::A_CLASS . self::A_TYPE . self::A_FUNCTION . '(1,2,' . self::ARRAY_TYPE . ')',
            $line);
    }

    public function test_givenASetOfExceptionTraceElementWithoutArgs_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoArguments(
    )
    {
        $lineAssembler = new LineAssembler();
        $lineAssembler->setLineNb(self::A_LINE_NB);
        $lineAssembler->setFile(self::A_FILE_PATH);
        $lineAssembler->setLine(self::A_LINE);
        $lineAssembler->setClass(self::A_CLASS);
        $lineAssembler->setType(self::A_TYPE);
        $lineAssembler->setFunction(self::A_FUNCTION);

        $line = $lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH . '(' . self::A_LINE . '): ' . self::A_CLASS . self::A_TYPE . self::A_FUNCTION . '()',
            $line);
    }

    public function test_givenASetOfExceptionTraceElementWithoutArgsAndFunction_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoFunctionOrType(
    )
    {
        $lineAssembler = new LineAssembler();
        $lineAssembler->setLineNb(self::A_LINE_NB);
        $lineAssembler->setFile(self::A_FILE_PATH);
        $lineAssembler->setLine(self::A_LINE);
        $lineAssembler->setClass(self::A_CLASS);

        $line = $lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH . '(' . self::A_LINE . '): ' . self::A_CLASS,
            $line);
    }

    public function test_givenASetOfExceptionTraceElementWithLineNbAndFileAndLine_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoClass(
    )
    {
        $lineAssembler = new LineAssembler();
        $lineAssembler->setLineNb(self::A_LINE_NB);
        $lineAssembler->setFile(self::A_FILE_PATH);
        $lineAssembler->setLine(self::A_LINE);
        $line = $lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH . '(' . self::A_LINE . '): ', $line);
    }

    public function test_givenASetOfExceptionTraceElementWithLineNbAndFile_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoLineNb(
    )
    {
        $lineAssembler = new LineAssembler();
        $lineAssembler->setLineNb(self::A_LINE_NB);
        $lineAssembler->setFile(self::A_FILE_PATH);

        $line = $lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH, $line);
    }

    public function test_givenASetOfExceptionTraceElementWithOnlyLineNb_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoFile(
    )
    {
        $lineAssembler = new LineAssembler();
        $lineAssembler->setLineNb(self::A_LINE_NB);

        $line = $lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ', $line);
    }

    public function test_givenNoExceptionTraceElement_buildExceptionString_shouldReturnAnEmptyLine()
    {
        $lineAssembler = new LineAssembler();
        $line = $lineAssembler->buildExceptionString();

        $this->assertEquals(self::EMPTY_LINE, $line);
    }

    public function test_stripBasePath_buildExceptionString_shouldStripBathPathFromFile() {
        $lineAssembler = new LineAssembler();
        $lineAssembler->setLineNb(self::A_LINE_NB);
        $lineAssembler->setFile(self::BASE_PATH . self::A_FILE_PATH);
        $lineAssembler->stripBasePath(self::BASE_PATH);

        $line = $lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH, $line);
    }

    public function test_nonMatchingBasePath_buildExceptionString_shouldNotStripBathPathFromFile() {
        $lineAssembler = new LineAssembler();
        $lineAssembler->setLineNb(self::A_LINE_NB);
        $lineAssembler->setFile(self::BASE_PATH . self::A_FILE_PATH);
        $lineAssembler->stripBasePath("/wrong/path/");

        $line = $lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::BASE_PATH . self::A_FILE_PATH, $line);
    }

    public function test_removeExtension_buildExceptionString_shouldRemoteFileExtension() {
        $lineAssembler = new LineAssembler();
        $lineAssembler->setLineNb(self::A_LINE_NB);
        $lineAssembler->setFile(self::FILE_WITHOUT_EXTENSION . self::EXTENSION);
        $lineAssembler->removeExtention(true);

        $line = $lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::FILE_WITHOUT_EXTENSION, $line);
    }
}
