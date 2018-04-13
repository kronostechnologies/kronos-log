<?php

namespace Kronos\Tests\Log\Formatter\Exception;

use Kronos\Log\Formatter\Exception\TraceBuilder;
use Throwable;

class TraceBuilderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $lineBuilder;

    /**
     * @var \Kronos\Log\Formatter\Exception\TraceBuilder
     */
    private $traceBuilder;

    public function setUp()
    {
        $this->lineBuilder = new \Kronos\Log\Formatter\Exception\LineBuilder();
        $this->traceBuilder = new \Kronos\Log\Formatter\Exception\TraceBuilder($this->lineBuilder);
    }

    public function test_Exception_getTraceAsString_shouldReturnFormattedExceptionStackTraceWithoutArguments()
    {
        $expectedString = '#0 /path/to/file/TestClass.php(20): TestClass->testFunction()
#1 /path/to/file/Trace.php(478): TestClass->testSomething()
#2 /path/to/file/App.php(197): Trace->callSomething()
#3 /path/to/file/App.php(59): App->checkSomething()
#4 /path/to/file/index.php(35): App->doSomething()';

        $actualString = $this->traceBuilder->getTraceAsString(new TestableException());

        $this->assertEquals($expectedString, $actualString);
    }

    public function test_IncludeArgumentsOption_getTraceAsString_shouldReturnFormattedExceptionStackTraceWithArguments()
    {
        $expectedString = "#0 /path/to/file/TestClass.php(20): TestClass->testFunction(1,2,Array)
#1 /path/to/file/Trace.php(478): TestClass->testSomething()
#2 /path/to/file/App.php(197): Trace->callSomething()
#3 /path/to/file/App.php(59): App->checkSomething()
#4 /path/to/file/index.php(35): App->doSomething()";
        $this->traceBuilder->includeArgs();

        $actualString = $this->traceBuilder->getTraceAsString(new TestableException());

        $this->assertEquals($expectedString, $actualString);
    }

    public function test_ShowTopLines_getTraceAsString_shouldReturnFormattedRequestedTopLinesFollowedByDots() {
        $expectedString = "#0 /path/to/file/TestClass.php(20): TestClass->testFunction()
#1 /path/to/file/Trace.php(478): TestClass->testSomething()
...";
        $this->traceBuilder->showTopLines(2);

        $actualString = $this->traceBuilder->getTraceAsString(new TestableException());

        $this->assertEquals($expectedString, $actualString);
    }

    public function test_ShowTopHigherOrEqualsToStackHeight_getTraceAsString_shouldReturnAllLinesFormatted() {
        $expectedString = "#0 /path/to/file/TestClass.php(20): TestClass->testFunction()
#1 /path/to/file/Trace.php(478): TestClass->testSomething()
#2 /path/to/file/App.php(197): Trace->callSomething()
#3 /path/to/file/App.php(59): App->checkSomething()
#4 /path/to/file/index.php(35): App->doSomething()";
        $this->traceBuilder->showTopLines(6);

        $actualString = $this->traceBuilder->getTraceAsString(new TestableException());

        $this->assertEquals($expectedString, $actualString);
    }

    public function test_ShowBottomLines_getTraceAsString_shouldReturnDotsFollowedFormattedRequestedBottomLines() {
        $expectedString = "...
#3 /path/to/file/App.php(59): App->checkSomething()
#4 /path/to/file/index.php(35): App->doSomething()";
        $this->traceBuilder->showBottomLines(2);

        $actualString = $this->traceBuilder->getTraceAsString(new TestableException());

        $this->assertEquals($expectedString, $actualString);
    }

    public function test_ShowBottomHigherOrEqualsToStackHeight_getTraceAsString_shouldReturnAllLinesFormatted()
    {
        $expectedString = "#0 /path/to/file/TestClass.php(20): TestClass->testFunction()
#1 /path/to/file/Trace.php(478): TestClass->testSomething()
#2 /path/to/file/App.php(197): Trace->callSomething()
#3 /path/to/file/App.php(59): App->checkSomething()
#4 /path/to/file/index.php(35): App->doSomething()";
        $this->traceBuilder->showBottomLines(6);

        $actualString = $this->traceBuilder->getTraceAsString(new TestableException());

        $this->assertEquals($expectedString, $actualString);
    }

    public function test_ShowTopAndBottomLines_getTraceAsString_shouldReturnFormattedRequestedTopLinesThenDotsAndBottomLines() {
        $expectedString = "#0 /path/to/file/TestClass.php(20): TestClass->testFunction()
...
#4 /path/to/file/index.php(35): App->doSomething()";
        $this->traceBuilder->showTopLines(1);
        $this->traceBuilder->showBottomLines(1);

        $actualString = $this->traceBuilder->getTraceAsString(new TestableException());

        $this->assertEquals($expectedString, $actualString);
    }

    public function test_OverlappingShowTopAndBottomLines_getTraceAsString_shouldReturnFormattedRequestedTopLinesThenDotsAndBottomLines() {
        $expectedString = "#0 /path/to/file/TestClass.php(20): TestClass->testFunction()
#1 /path/to/file/Trace.php(478): TestClass->testSomething()
#2 /path/to/file/App.php(197): Trace->callSomething()
#3 /path/to/file/App.php(59): App->checkSomething()
#4 /path/to/file/index.php(35): App->doSomething()";
        $this->traceBuilder->showTopLines(3);
        $this->traceBuilder->showBottomLines(3);

        $actualString = $this->traceBuilder->getTraceAsString(new TestableException());

        $this->assertEquals($expectedString, $actualString);
    }
}

class TestableException //Once we support PHP 7 => implements \Throwable
{
    public function getTrace()
    {
        return [
            0 => [
                'file' => '/path/to/file/TestClass.php',
                'line' => 20,
                'function' => 'testFunction',
                'class' => 'TestClass',
                'type' => '->',
                'args' => [
                    0 => 1,
                    1 => 2,
                    2 => [
                        'test' => 'test_value'
                    ],
                ],
            ],
            1 => [
                'file' => '/path/to/file/Trace.php',
                'line' => 478,
                'function' => 'testSomething',
                'class' => 'TestClass',
                'type' => '->',
                'args' => [],
            ],
            2 => [
                'file' => '/path/to/file/App.php',
                'line' => 197,
                'function' => 'callSomething',
                'class' => 'Trace',
                'type' => '->',
                'args' => [],
            ],
            3 => [
                'file' => '/path/to/file/App.php',
                'line' => 59,
                'function' => 'checkSomething',
                'class' => 'App',
                'type' => '->',
                'args' => [],
            ],
            4 => [
                'file' => '/path/to/file/index.php',
                'line' => 35,
                'function' => 'doSomething',
                'class' => 'App',
                'type' => '->',
                'args' => [],
            ]
        ];
    }
}