<?php

namespace Kronos\Tests\Log\Formatter\Exception;

use Kronos\Log\Formatter\Exception\LineAssembler;
use Kronos\Log\Formatter\Exception\NamespaceShrinker;

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
    const SHRUNK_CLASSNAME = "Shrunk Classname";
    const SHRUNK_PATH = 'shrunk path';

    /**
     * @var NamespaceShrinker
     */
    private $namespaceShrinker;

    /**
     * @var LineAssembler
     */
    private $lineAssembler;

    public function setUp(): void
    {
        $this->namespaceShrinker = $this->createMock(NamespaceShrinker::class);
        $this->lineAssembler = new LineAssembler($this->namespaceShrinker);
    }

    public function test_givenACompleteSetOfExceptionTraceElements_buildExceptionString_shouldReturnAFormattedLineWithAllElements(
    )
    {
        $this->lineAssembler->setLineNb(self::A_LINE_NB);
        $this->lineAssembler->setFile(self::A_FILE_PATH);
        $this->lineAssembler->setLine(self::A_LINE);
        $this->lineAssembler->setClass(self::A_CLASS);
        $this->lineAssembler->setType(self::A_TYPE);
        $this->lineAssembler->setFunction(self::A_FUNCTION);
        $this->lineAssembler->setArgs(self::SOME_ARGS);

        $line = $this->lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH . '(' . self::A_LINE . '): ' . self::A_CLASS . self::A_TYPE . self::A_FUNCTION . '()',
            $line);
    }

    public function test_allTraceElementsAndIncludeArgs_buildExceptionString_shouldReturnAFormattedLineWithAllElementsWithArgs(
    )
    {
        $this->lineAssembler->includeArgs();
        $this->lineAssembler->setLineNb(self::A_LINE_NB);
        $this->lineAssembler->setFile(self::A_FILE_PATH);
        $this->lineAssembler->setLine(self::A_LINE);
        $this->lineAssembler->setClass(self::A_CLASS);
        $this->lineAssembler->setType(self::A_TYPE);
        $this->lineAssembler->setFunction(self::A_FUNCTION);
        $this->lineAssembler->setArgs(self::SOME_ARGS);

        $line = $this->lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH . '(' . self::A_LINE . '): ' . self::A_CLASS . self::A_TYPE . self::A_FUNCTION . '(1,2,' . self::ARRAY_TYPE . ')',
            $line);
    }

    public function test_givenASetOfExceptionTraceElementWithoutArgs_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoArguments(
    )
    {
        $this->lineAssembler->setLineNb(self::A_LINE_NB);
        $this->lineAssembler->setFile(self::A_FILE_PATH);
        $this->lineAssembler->setLine(self::A_LINE);
        $this->lineAssembler->setClass(self::A_CLASS);
        $this->lineAssembler->setType(self::A_TYPE);
        $this->lineAssembler->setFunction(self::A_FUNCTION);

        $line = $this->lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH . '(' . self::A_LINE . '): ' . self::A_CLASS . self::A_TYPE . self::A_FUNCTION . '()',
            $line);
    }

    public function test_givenASetOfExceptionTraceElementWithoutArgsAndFunction_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoFunctionOrType(
    )
    {
        $this->lineAssembler->setLineNb(self::A_LINE_NB);
        $this->lineAssembler->setFile(self::A_FILE_PATH);
        $this->lineAssembler->setLine(self::A_LINE);
        $this->lineAssembler->setClass(self::A_CLASS);

        $line = $this->lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH . '(' . self::A_LINE . '): ' . self::A_CLASS,
            $line);
    }

    public function test_classAndShrinkNamespaces_buildExceptionString_shouldReturnShrunkClassname()
    {
        $this->namespaceShrinker
            ->expects(self::once())
            ->method('shrink')
            ->with(self::A_CLASS)
            ->willReturn(self::SHRUNK_CLASSNAME);
        $this->lineAssembler->shrinkNamespaces(true);
        $this->lineAssembler->setLineNb(self::A_LINE_NB);
        $this->lineAssembler->setFile(self::A_FILE_PATH);
        $this->lineAssembler->setLine(self::A_LINE);
        $this->lineAssembler->setClass(self::A_CLASS);

        $line = $this->lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH . '(' . self::A_LINE . '): ' . self::SHRUNK_CLASSNAME,
            $line);
    }

    public function test_givenASetOfExceptionTraceElementWithLineNbAndFileAndLine_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoClass(
    )
    {
        $this->lineAssembler->setLineNb(self::A_LINE_NB);
        $this->lineAssembler->setFile(self::A_FILE_PATH);
        $this->lineAssembler->setLine(self::A_LINE);
        $line = $this->lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH . '(' . self::A_LINE . '): ', $line);
    }

    public function test_givenASetOfExceptionTraceElementWithLineNbAndFile_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoLineNb(
    )
    {
        $this->lineAssembler->setLineNb(self::A_LINE_NB);
        $this->lineAssembler->setFile(self::A_FILE_PATH);

        $line = $this->lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH, $line);
    }

    public function test_givenASetOfExceptionTraceElementWithOnlyLineNb_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoFile(
    )
    {
        $this->lineAssembler->setLineNb(self::A_LINE_NB);

        $line = $this->lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ', $line);
    }

    public function test_givenNoExceptionTraceElement_buildExceptionString_shouldReturnAnEmptyLine()
    {
        $line = $this->lineAssembler->buildExceptionString();

        $this->assertEquals(self::EMPTY_LINE, $line);
    }

    public function test_stripBasePath_buildExceptionString_shouldStripBathPathFromFile()
    {
        $this->lineAssembler->setLineNb(self::A_LINE_NB);
        $this->lineAssembler->setFile(self::BASE_PATH . self::A_FILE_PATH);
        $this->lineAssembler->stripBasePath(self::BASE_PATH);

        $line = $this->lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::A_FILE_PATH, $line);
    }

    public function test_shrinkPaths_buildExceptionString_shouldShrinkPathFromFile()
    {
        $this->namespaceShrinker
            ->expects(self::once())
            ->method('shrinkUsingSeparator')
            ->with(self::A_FILE_PATH, DIRECTORY_SEPARATOR)
            ->willReturn(self::SHRUNK_PATH);
        $this->lineAssembler->shrinkPaths(true);
        $this->lineAssembler->setLineNb(self::A_LINE_NB);
        $this->lineAssembler->setFile(self::A_FILE_PATH);

        $line = $this->lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::SHRUNK_PATH, $line);
    }

    public function test_nonMatchingBasePath_buildExceptionString_shouldNotStripBathPathFromFile()
    {
        $this->lineAssembler->setLineNb(self::A_LINE_NB);
        $this->lineAssembler->setFile(self::BASE_PATH . self::A_FILE_PATH);
        $this->lineAssembler->stripBasePath("/wrong/path/");

        $line = $this->lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::BASE_PATH . self::A_FILE_PATH, $line);
    }

    public function test_removeExtension_buildExceptionString_shouldRemoteFileExtension()
    {
        $this->lineAssembler->setLineNb(self::A_LINE_NB);
        $this->lineAssembler->setFile(self::FILE_WITHOUT_EXTENSION . self::EXTENSION);
        $this->lineAssembler->removeExtension(true);

        $line = $this->lineAssembler->buildExceptionString();

        $this->assertEquals('#' . self::A_LINE_NB . ' ' . self::FILE_WITHOUT_EXTENSION, $line);
    }
}
