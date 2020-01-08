<?php

namespace Kronos\Tests\Log\Formatter\Exception;

use Kronos\Log\Formatter\Exception\LineAssembler;
use Kronos\Log\Formatter\Exception\LineAssemblerBuilder;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

class TraceBuilderTest extends \PHPUnit\Framework\TestCase
{
    const PATH_TO_FILE = "/path/to/file/";
    /**
     * @var MockObject|LineAssemblerBuilder
     */
    private $lineAssemblerBuilder;

    /**
     * @var TraceBuilder
     */
    private $traceBuilder;

    public function setUp(): void
    {
        $this->lineAssemblerBuilder = $this->createMock(LineAssemblerBuilder::class);
        $this->lineAssemblerBuilder->method('buildAssembler')->willReturnCallback(function() {
            return new LineAssembler();
        });

        $this->traceBuilder = new TraceBuilder($this->lineAssemblerBuilder);
    }

    public function test_traceBuilder_stripBasePath_shouldSendBaseBaseToLineAssemblerBuilder()
    {
        $this->lineAssemblerBuilder
            ->expects(self::once())
            ->method("stripBasePath")
            ->with(self::PATH_TO_FILE);

        $this->traceBuilder->stripBasePath(self::PATH_TO_FILE);
    }

    public function test_traceBuilder_removeExtension_shouldTellLineAssemblerBuilderToRemoveExtension()
    {
        $this->lineAssemblerBuilder
            ->expects(self::once())
            ->method("removeExtension")
            ->with(true);

        $this->traceBuilder->removeExtension(true);
    }

    public function test_exception_getTtraceAsString_shouldBuildLineAssemblerForEachStackItem(): void
    {
        $exception = $this->givenException();
        $this->lineAssemblerBuilder
            ->expects(self::exactly(5))
            ->method('buildAssembler');

        $this->traceBuilder->getTraceAsString($exception);
    }

    public function test_Exception_getTraceAsString_shouldReturnFormattedExceptionStackTraceWithoutArguments()
    {
        $exception = $this->givenException();
        $expectedString = '#0 /path/to/file/TestClass.php(20): TestClass->testFunction()
#1 /path/to/file/Trace.php(478): TestClass->testSomething()
#2 /path/to/file/App.php(197): Trace->callSomething()
#3 /path/to/file/App.php(59): App->checkSomething()
#4 /path/to/file/index.php(35): App->doSomething()';

        $actualString = $this->traceBuilder->getTraceAsString($exception);

        $this->assertEquals($expectedString, $actualString);
    }

    public function test_IncludeArgumentsOption_getTraceAsString_shouldReturnFormattedExceptionStackTraceWithArguments()
    {
        $exception = $this->givenException();
        $expectedString = "#0 /path/to/file/TestClass.php(20): TestClass->testFunction(1,2,Array)
#1 /path/to/file/Trace.php(478): TestClass->testSomething()
#2 /path/to/file/App.php(197): Trace->callSomething()
#3 /path/to/file/App.php(59): App->checkSomething()
#4 /path/to/file/index.php(35): App->doSomething()";
        $this->traceBuilder->includeArgs();

        $actualString = $this->traceBuilder->getTraceAsString($exception);

        $this->assertEquals($expectedString, $actualString);
    }

    public function test_ShowTopLines_getTraceAsString_shouldReturnFormattedRequestedTopLinesFollowedByDots()
    {
        $exception = $this->givenException();
        $expectedString = "#0 /path/to/file/TestClass.php(20): TestClass->testFunction()
#1 /path/to/file/Trace.php(478): TestClass->testSomething()
...";
        $this->traceBuilder->showTopLines(2);

        $actualString = $this->traceBuilder->getTraceAsString($exception);

        $this->assertEquals($expectedString, $actualString);
    }

    public function test_ShowTopHigherOrEqualsToStackHeight_getTraceAsString_shouldReturnAllLinesFormatted()
    {
        $exception = $this->givenException();
        $expectedString = "#0 /path/to/file/TestClass.php(20): TestClass->testFunction()
#1 /path/to/file/Trace.php(478): TestClass->testSomething()
#2 /path/to/file/App.php(197): Trace->callSomething()
#3 /path/to/file/App.php(59): App->checkSomething()
#4 /path/to/file/index.php(35): App->doSomething()";
        $this->traceBuilder->showTopLines(6);

        $actualString = $this->traceBuilder->getTraceAsString($exception);

        $this->assertEquals($expectedString, $actualString);
    }

    public function test_ShowBottomLines_getTraceAsString_shouldReturnDotsFollowedFormattedRequestedBottomLines()
    {
        $exception = $this->givenException();
        $expectedString = "...
#3 /path/to/file/App.php(59): App->checkSomething()
#4 /path/to/file/index.php(35): App->doSomething()";
        $this->traceBuilder->showBottomLines(2);

        $actualString = $this->traceBuilder->getTraceAsString($exception);

        $this->assertEquals($expectedString, $actualString);
    }

    public function test_ShowBottomHigherOrEqualsToStackHeight_getTraceAsString_shouldReturnAllLinesFormatted()
    {
        $exception = $this->givenException();
        $expectedString = "#0 /path/to/file/TestClass.php(20): TestClass->testFunction()
#1 /path/to/file/Trace.php(478): TestClass->testSomething()
#2 /path/to/file/App.php(197): Trace->callSomething()
#3 /path/to/file/App.php(59): App->checkSomething()
#4 /path/to/file/index.php(35): App->doSomething()";
        $this->traceBuilder->showBottomLines(6);

        $actualString = $this->traceBuilder->getTraceAsString($exception);

        $this->assertEquals($expectedString, $actualString);
    }

    public function test_ShowTopAndBottomLines_getTraceAsString_shouldReturnFormattedRequestedTopLinesThenDotsAndBottomLines(
    )
    {
        $exception = $this->givenException();
        $expectedString = "#0 /path/to/file/TestClass.php(20): TestClass->testFunction()
...
#4 /path/to/file/index.php(35): App->doSomething()";
        $this->traceBuilder->showTopLines(1);
        $this->traceBuilder->showBottomLines(1);

        $actualString = $this->traceBuilder->getTraceAsString($exception);

        $this->assertEquals($expectedString, $actualString);
    }

    public function test_OverlappingShowTopAndBottomLines_getTraceAsString_shouldReturnFormattedRequestedTopLinesThenDotsAndBottomLines(
    )
    {
        $exception = $this->givenException();
        $expectedString = "#0 /path/to/file/TestClass.php(20): TestClass->testFunction()
#1 /path/to/file/Trace.php(478): TestClass->testSomething()
#2 /path/to/file/App.php(197): Trace->callSomething()
#3 /path/to/file/App.php(59): App->checkSomething()
#4 /path/to/file/index.php(35): App->doSomething()";
        $this->traceBuilder->showTopLines(3);
        $this->traceBuilder->showBottomLines(3);

        $actualString = $this->traceBuilder->getTraceAsString($exception);

        $this->assertEquals($expectedString, $actualString);
    }


    private function givenException(): \Exception
    {
        // Dark magic to override the final exception trace
        $exception = new \Exception();
        $reflection = new \ReflectionObject($exception);
        $property = $reflection->getProperty('trace');
        $property->setAccessible(true);
        $property->setValue($exception, [
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
        ]);

        return $exception;
    }
}
