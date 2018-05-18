<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\ExceptionTraceHelper;
use Kronos\Log\Factory\Formatter;
use Kronos\Log\Formatter\Exception\TraceBuilder;

class ExceptionTraceHelperTest extends \PHPUnit_Framework_TestCase
{
    const TOP_LINES = 4;
    const LOWER_THAN_ONE = -1;
    const BOTTOM_LINES = 2;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $traceBuilder;

    /**
     * @var ExceptionTraceHelper
     */
    private $helper;

    public function setUp()
    {
        $this->factory = $this->getMockWithoutInvokingTheOriginalConstructor(Formatter::class);

        $this->helper = new ExceptionTraceHelper($this->factory);
    }

    public function test_EmptySettings_getExceptionTraceBuilderForSettings_ShouldCreateAndReturnTraceBuilder()
    {
        $settings = [];
        $expectedTraceBuilder = $this->getMockWithoutInvokingTheOriginalConstructor(TraceBuilder::class);
        $this->factory
            ->expects(self::once())
            ->method('createExceptionTraceBuilder')
            ->willReturn($expectedTraceBuilder);
        $expectedTraceBuilder->expects(self::never())->method('includeArgs');
        $expectedTraceBuilder->expects(self::never())->method('showTopLines');
        $expectedTraceBuilder->expects(self::never())->method('showBottomLines');

        $actualTraceBuilder = $this->helper->getExceptionTraceBuilderForSettings($settings);

        $this->assertSame($expectedTraceBuilder, $actualTraceBuilder);
    }

    public function test_ShowExceptionStackTraceSettingSetToFalse_getExceptionTraceBuilderForSettings_ShouldNotCreateTraceBuilderAndReturnNull(
    )
    {
        $settings = [
            ExceptionTraceHelper::SHOW_EXCEPTION_STACKTRACE => false
        ];
        $this->factory
            ->expects(self::never())
            ->method('createExceptionTraceBuilder');

        $null = $this->helper->getExceptionTraceBuilderForSettings($settings);

        $this->assertNull($null);
    }

    public function test_IncludeArgsSetToTrue_getExceptionTraceBuilderForSettings_ShouldSetIncludeArgs() {
        $settings = [
            ExceptionTraceHelper::INCLUDE_ARGS => true
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('includeArgs');

        $this->helper->getExceptionTraceBuilderForSettings($settings);
    }

    public function test_ShowExceptionTopLines_getExceptionTraceBuilderForSettings_ShouldSetShowTopLines() {
        $settings = [
            ExceptionTraceHelper::SHOW_EXCEPTION_TOP_LINES => self::TOP_LINES
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('showTopLines')
            ->with(self::TOP_LINES);

        $this->helper->getExceptionTraceBuilderForSettings($settings);
    }

    public function test_LowerThanOneTopLines_getExceptionTraceBuilderForSettings_ShouldNotSetShowTopLines() {
        $settings = [
            ExceptionTraceHelper::SHOW_EXCEPTION_TOP_LINES => self::LOWER_THAN_ONE
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::never())
            ->method('showTopLines');

        $this->helper->getExceptionTraceBuilderForSettings($settings);
    }

    public function test_ShowExceptionBottomLines_getExceptionTraceBuilderForSettings_ShouldSetShowBottomLines() {
        $settings = [
            ExceptionTraceHelper::SHOW_EXCEPTION_BOTTOM_LINES => self::BOTTOM_LINES
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('showBottomLines')
            ->with(self::BOTTOM_LINES);

        $this->helper->getExceptionTraceBuilderForSettings($settings);
    }

    public function test_LowerThanOneBottomLines_getExceptionTraceBuilderForSettings_ShouldNotSetShowBottomLines() {
        $settings = [
            ExceptionTraceHelper::SHOW_EXCEPTION_BOTTOM_LINES => self::LOWER_THAN_ONE
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::never())
            ->method('showBottomLines');

        $this->helper->getExceptionTraceBuilderForSettings($settings);
    }

    public function test_EmptySettings_getPreviousExceptionTraceBuilderForSettings_ShouldCreateAndReturnTraceBuilder()
    {
        $settings = [];
        $expectedTraceBuilder = $this->getMockWithoutInvokingTheOriginalConstructor(TraceBuilder::class);
        $this->factory
            ->expects(self::once())
            ->method('createExceptionTraceBuilder')
            ->willReturn($expectedTraceBuilder);
        $expectedTraceBuilder->expects(self::never())->method('includeArgs');
        $expectedTraceBuilder->expects(self::never())->method('showTopLines');
        $expectedTraceBuilder->expects(self::never())->method('showBottomLines');

        $actualTraceBuilder = $this->helper->getPreviousExceptionTraceBuilderForSettings($settings);

        $this->assertSame($expectedTraceBuilder, $actualTraceBuilder);
    }

    public function test_ShowExceptionStackTraceSettingSetToFalse_getPreviousExceptionTraceBuilderForSettings_ShouldNotCreateTraceBuilderAndReturnNull(
    )
    {
        $settings = [
            ExceptionTraceHelper::SHOW_PREVIOUS_EXCEPTION_STACKTRACE => false
        ];
        $this->factory
            ->expects(self::never())
            ->method('createExceptionTraceBuilder');

        $null = $this->helper->getPreviousExceptionTraceBuilderForSettings($settings);

        $this->assertNull($null);
    }

    public function test_IncludeArgsSetToTrue_getPreviousExceptionTraceBuilderForSettings_ShouldSetIncludeArgs() {
        $settings = [
            ExceptionTraceHelper::INCLUDE_ARGS => true
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('includeArgs');

        $this->helper->getPreviousExceptionTraceBuilderForSettings($settings);
    }

    public function test_ShowExceptionTopLines_getPreviousExceptionTraceBuilderForSettings_ShouldSetShowTopLines() {
        $settings = [
            ExceptionTraceHelper::SHOW_PREVIOUS_EXCEPTION_TOP_LINES => self::TOP_LINES
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('showTopLines')
            ->with(self::TOP_LINES);

        $this->helper->getPreviousExceptionTraceBuilderForSettings($settings);
    }

    public function test_LowerThanOneTopLines_getPreviousExceptionTraceBuilderForSettings_ShouldNotSetShowTopLines() {
        $settings = [
            ExceptionTraceHelper::SHOW_PREVIOUS_EXCEPTION_TOP_LINES => self::LOWER_THAN_ONE
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::never())
            ->method('showTopLines');

        $this->helper->getPreviousExceptionTraceBuilderForSettings($settings);
    }

    public function test_ShowExceptionBottomLines_getPreviousExceptionTraceBuilderForSettings_ShouldSetShowBottomLines() {
        $settings = [
            ExceptionTraceHelper::SHOW_PREVIOUS_EXCEPTION_BOTTOM_LINES => self::BOTTOM_LINES
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('showBottomLines')
            ->with(self::BOTTOM_LINES);

        $this->helper->getPreviousExceptionTraceBuilderForSettings($settings);
    }

    public function test_LowerThanOneBottomLines_getPreviousExceptionTraceBuilderForSettings_ShouldNotSetShowBottomLines() {
        $settings = [
            ExceptionTraceHelper::SHOW_PREVIOUS_EXCEPTION_BOTTOM_LINES => self::LOWER_THAN_ONE
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::never())
            ->method('showBottomLines');

        $this->helper->getPreviousExceptionTraceBuilderForSettings($settings);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function givenTraceBuilder()
    {
        $traceBuilder = $this->getMockWithoutInvokingTheOriginalConstructor(TraceBuilder::class);
        $this->factory
            ->method('createExceptionTraceBuilder')
            ->willReturn($traceBuilder);
        return $traceBuilder;
    }
}