<?php

namespace Kronos\Tests\Log;

use Kronos\Log\Formatter\Exception\TraceBuilder;

class ExceptionTraceBuilderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $line_builder;

    /**
     * @var \Kronos\Log\Formatter\Exception\TraceBuilder
     */
    private $exception_trace_builder;

    public function setUp()
    {
        $this->line_builder = new \Kronos\Log\Formatter\Exception\LineBuilder();
        $this->exception_trace_builder = new \Kronos\Log\Formatter\Exception\TraceBuilder($this->line_builder);
    }

    public function test_givenAnExceptionTraceArrayWithNoIncludeArgumentsOption_getTraceAsString_shouldReturnFormattedExceptionStackTraceWithoutArguments(
    )
    {
        $trace_as_string = $this->exception_trace_builder->getTraceAsString(new TestableException(), false);

        $this->assertEquals($this->thenAFormattedStackAsTraceWithoutArguments(), $trace_as_string);
    }

    public function test_givenAnExceptionTraceArrayWithIncludeArgumentsOption_getTraceAsString_shouldReturnFormattedExceptionStackTraceWithArguments(
    )
    {
        $trace_as_string = $this->exception_trace_builder->getTraceAsString(new TestableException(), true);

        $this->assertEquals($this->thenAFormattedStackAsTraceWithArguments(), $trace_as_string);
    }

    public function thenAFormattedStackAsTraceWithoutArguments()
    {
        return '#0 /path/to/file/TestClass.php(20): TestClass->testFunction()
#1 /path/to/file/Tool.php(478): TestClass->runTool()
#2 /path/to/file/CLI.php(197): Tool->run()
#3 /path/to/file/CLI.php(59): CLI->runTool()
#4 /path/to/file/tool.php(35): CLI->run()
#5 /path/to/file/tool(4): includeTest()
';
    }

    public function thenAFormattedStackAsTraceWithArguments()
    {
        return '#0 /path/to/file/TestClass.php(20): TestClass->testFunction(1,2,Array)
#1 /path/to/file/Tool.php(478): TestClass->runTool()
#2 /path/to/file/CLI.php(197): Tool->run()
#3 /path/to/file/CLI.php(59): CLI->runTool()
#4 /path/to/file/tool.php(35): CLI->run()
#5 /path/to/file/tool(4): includeTest(/path/to/file/tool.php)
';
    }
}

class TestableException
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
                'file' => '/path/to/file/Tool.php',
                'line' => 478,
                'function' => 'runTool',
                'class' => 'TestClass',
                'type' => '->',
                'args' => [],
            ],
            2 => [
                'file' => '/path/to/file/CLI.php',
                'line' => 197,
                'function' => 'run',
                'class' => 'Tool',
                'type' => '->',
                'args' => [],
            ],
            3 => [
                'file' => '/path/to/file/CLI.php',
                'line' => 59,
                'function' => 'runTool',
                'class' => 'CLI',
                'type' => '->',
                'args' => [],
            ],
            4 => [
                'file' => '/path/to/file/tool.php',
                'line' => 35,
                'function' => 'run',
                'class' => 'CLI',
                'type' => '->',
                'args' => [],
            ],
            5 => [
                'file' => '/path/to/file/tool',
                'line' => 4,
                'function' => 'includeTest',
                'args' => [
                    0 => '/path/to/file/tool.php'
                ],
            ]
        ];
    }
}