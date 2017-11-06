<?php

namespace Kronos\Tests\Log;

class LineBuilderTest extends \PHPUnit_Framework_TestCase {

	const A_LINE_NB = 0;
	const A_FILE_PATH = '/path/to/file/Testerino.php';
	const A_LINE = 20;
	const A_CLASS = 'Testerino';
	const A_TYPE = '->';
	const A_FUNCTION = 'a';
	const SOME_ARGS = [1,2,['test']];

	public function test_givenACompleteSetOfExceptionTraceElements_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndArguments(){
		$line_builder = new \Kronos\Log\Exception\LineBuilder();

		$line_builder->setLineNb(self::A_LINE_NB);
		$line_builder->setFile(self::A_FILE_PATH);
		$line_builder->setLine(self::A_LINE);
		$line_builder->setClass(self::A_CLASS);
		$line_builder->setType(self::A_TYPE);
		$line_builder->setFunction(self::A_FUNCTION);
		$line_builder->setArgs(self::SOME_ARGS);

		$line = $line_builder->buildExceptionString();

		$this->assertEquals($this->thenAFormattedLineWithAllElementsAndArguments(), $line);
	}

	public function test_givenASetOfExceptionTraceElementWithoutArgs_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoArguments(){
		$line_builder = new \Kronos\Log\Exception\LineBuilder();

		$line_builder->setLineNb(self::A_LINE_NB);
		$line_builder->setFile(self::A_FILE_PATH);
		$line_builder->setLine(self::A_LINE);
		$line_builder->setClass(self::A_CLASS);
		$line_builder->setType(self::A_TYPE);
		$line_builder->setFunction(self::A_FUNCTION);

		$line = $line_builder->buildExceptionString();

		$this->assertEquals($this->thenAFormattedLineWithAllElementsAndNoArguments(), $line);
	}

	public function test_givenASetOfExceptionTraceElementWithoutArgsAndFunction_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoFunctionOrType(){
		$line_builder = new \Kronos\Log\Exception\LineBuilder();

		$line_builder->setLineNb(self::A_LINE_NB);
		$line_builder->setFile(self::A_FILE_PATH);
		$line_builder->setLine(self::A_LINE);
		$line_builder->setClass(self::A_CLASS);

		$line = $line_builder->buildExceptionString();

		$this->assertEquals($this->thenAFormattedLineWithAllElementsAndNoFunctionOrType(), $line);
	}

	public function test_givenASetOfExceptionTraceElementWithLineNbAndFileAndLine_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoClass(){
		$line_builder = new \Kronos\Log\Exception\LineBuilder();

		$line_builder->setLineNb(self::A_LINE_NB);
		$line_builder->setFile(self::A_FILE_PATH);
		$line_builder->setLine(self::A_LINE);

		$line = $line_builder->buildExceptionString();

		$this->assertEquals($this->thenAFormattedLineWithAllElementsAndNoClass(), $line);
	}

	public function test_givenASetOfExceptionTraceElementWithLineNbAndFile_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoLineNb(){
		$line_builder = new \Kronos\Log\Exception\LineBuilder();

		$line_builder->setLineNb(self::A_LINE_NB);
		$line_builder->setFile(self::A_FILE_PATH);

		$line = $line_builder->buildExceptionString();

		$this->assertEquals($this->thenAFormattedLineWithAllElementsAndNoLineNb(), $line);
	}

	public function test_givenASetOfExceptionTraceElementWithOnlyLineNb_buildExceptionString_shouldReturnAFormattedLineWithAllElementsAndNoFile(){
		$line_builder = new \Kronos\Log\Exception\LineBuilder();

		$line_builder->setLineNb(self::A_LINE_NB);

		$line = $line_builder->buildExceptionString();

		$this->assertEquals($this->thenAFormattedLineWithAllElementsAndNoFile(), $line);
	}

	public function test_givenNoExceptionTraceElement_buildExceptionString_shouldReturnAnEmptyLine(){
		$line_builder = new \Kronos\Log\Exception\LineBuilder();

		$line = $line_builder->buildExceptionString();

		$this->assertEquals($this->thenAnEmptyLine(), $line);
	}

	public function test_a_clearLine_shouldReturnAnEmptyLine2(){
		$line_builder = new \Kronos\Log\Exception\LineBuilder();

		$line_builder->setLineNb(self::A_LINE_NB);
		$line_builder->setFile(self::A_FILE_PATH);
		$line_builder->setLine(self::A_LINE);
		$line_builder->setClass(self::A_CLASS);
		$line_builder->setType(self::A_TYPE);
		$line_builder->setFunction(self::A_FUNCTION);
		$line_builder->setArgs(self::SOME_ARGS);

		$line = $line_builder->buildExceptionString();

		$line_builder->clearLine();

		$line = $line_builder->buildExceptionString();

		$this->assertEquals($this->thenAnEmptyLine(), $line);
	}

	public function thenAFormattedLineWithAllElementsAndArguments(){
		return '#0 /path/to/file/Testerino.php(20): Testerino->a(1,2,Array)';
	}

	public function thenAFormattedLineWithAllElementsAndNoArguments(){
		return '#0 /path/to/file/Testerino.php(20): Testerino->a()';
	}

	public function thenAFormattedLineWithAllElementsAndNoFunctionOrType(){
		return '#0 /path/to/file/Testerino.php(20): Testerino';
	}

	public function thenAFormattedLineWithAllElementsAndNoClass(){
		return '#0 /path/to/file/Testerino.php(20): ';
	}

	public function thenAFormattedLineWithAllElementsAndNoLineNb(){
		return '#0 /path/to/file/Testerino.php';
	}

	public function thenAFormattedLineWithAllElementsAndNoFile(){
		return '#0 ';
	}

	public function thenAnEmptyLine(){
		return "";
	}

}