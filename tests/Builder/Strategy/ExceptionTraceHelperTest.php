<?php

namespace Kronos\Tests\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy\ExceptionTraceHelper;
use Kronos\Log\Factory\Formatter;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExceptionTraceHelperTest extends TestCase
{
    const TOP_LINES = 4;
    const LOWER_THAN_ONE = -1;
    const BOTTOM_LINES = 2;
    const BASE_PATH = '/base/path/';
    /**
     * @var MockObject&Formatter
     */
    private $factory;

    /**
     * @var ExceptionTraceHelper
     */
    private $helper;

    public function setUp(): void
    {
        $this->factory = $this->createMock(Formatter::class);

        $this->helper = new ExceptionTraceHelper($this->factory);
    }

    public function test_EmptySettings_getExceptionTraceBuilderForSettings_ShouldCreateAndReturnTraceBuilder()
    {
        $settings = [];
        $expectedTraceBuilder = $this->createMock(TraceBuilder::class);
        $this->factory
            ->expects(self::once())
            ->method('createExceptionTraceBuilder')
            ->willReturn($expectedTraceBuilder);
        $expectedTraceBuilder->expects(self::never())->method('includeArgs');
        $expectedTraceBuilder->expects(self::never())->method('stripBasePath');
        $expectedTraceBuilder->expects(self::never())->method('removeExtension');
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

    public function test_IncludeArgsSetToTrue_getExceptionTraceBuilderForSettings_ShouldSetIncludeArgs()
    {
        $settings = [
            ExceptionTraceHelper::INCLUDE_ARGS => true
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('includeArgs');

        $this->helper->getExceptionTraceBuilderForSettings($settings);
    }

    public function test_StripExceptionBasePath_getExceptionTraceBuilderForSettings_ShouldStripBasePath()
    {
        $settings = [
            ExceptionTraceHelper::STRIP_BASE_PATH => self::BASE_PATH
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('stripBasePath')
            ->with(self::BASE_PATH);

        $this->helper->getExceptionTraceBuilderForSettings($settings);
    }

    public function test_ShrinkExceptionPathsSetToTrue_getExceptionTraceBuilderForSettings_ShouldShrinkPaths()
    {
        $settings = [
            ExceptionTraceHelper::SHRINK_PATHS => true
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('shrinkPaths')
            ->with(true);

        $this->helper->getExceptionTraceBuilderForSettings($settings);
    }

    public function test_RemoveExceptionFileExtensionSetToTrue_getExceptionTraceBuilderForSettings_ShouldRemoveExtension()
    {
        $settings = [
            ExceptionTraceHelper::REMOVE_EXTENSION => true
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('removeExtension')
            ->with(true);

        $this->helper->getExceptionTraceBuilderForSettings($settings);
    }

    public function test_ShrinkExceptionNamespacesSetToTrue_getExceptionTraceBuilderForSettings_ShouldShrinkNamespaces()
    {
        $settings = [
            ExceptionTraceHelper::SHRINK_NAMESPACES => true
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('shrinkNamespaces')
            ->with(true);

        $this->helper->getExceptionTraceBuilderForSettings($settings);
    }

    public function test_ShowExceptionTopLines_getExceptionTraceBuilderForSettings_ShouldSetShowTopLines()
    {
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

    public function test_LowerThanOneTopLines_getExceptionTraceBuilderForSettings_ShouldNotSetShowTopLines()
    {
        $settings = [
            ExceptionTraceHelper::SHOW_EXCEPTION_TOP_LINES => self::LOWER_THAN_ONE
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::never())
            ->method('showTopLines');

        $this->helper->getExceptionTraceBuilderForSettings($settings);
    }

    public function test_ShowExceptionBottomLines_getExceptionTraceBuilderForSettings_ShouldSetShowBottomLines()
    {
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

    public function test_LowerThanOneBottomLines_getExceptionTraceBuilderForSettings_ShouldNotSetShowBottomLines()
    {
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
        $expectedTraceBuilder = $this->createMock(TraceBuilder::class);
        $this->factory
            ->expects(self::once())
            ->method('createExceptionTraceBuilder')
            ->willReturn($expectedTraceBuilder);
        $expectedTraceBuilder->expects(self::never())->method('includeArgs');
        $expectedTraceBuilder->expects(self::never())->method('stripBasePath');
        $expectedTraceBuilder->expects(self::never())->method('removeExtension');
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

    public function test_IncludeArgsSetToTrue_getPreviousExceptionTraceBuilderForSettings_ShouldSetIncludeArgs()
    {
        $settings = [
            ExceptionTraceHelper::INCLUDE_ARGS => true
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('includeArgs');

        $this->helper->getPreviousExceptionTraceBuilderForSettings($settings);
    }

    public function test_StripExceptionBasePath_getPreviousExceptionTraceBuilderForSettings_ShouldStripBasePath()
    {
        $settings = [
            ExceptionTraceHelper::STRIP_BASE_PATH => self::BASE_PATH
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('stripBasePath')
            ->with(self::BASE_PATH);

        $this->helper->getPreviousExceptionTraceBuilderForSettings($settings);
    }

    public function test_ShrinkExceptionPathsSetToTrue_getPreviousExceptionTraceBuilderForSettings_ShouldShrinkPaths()
    {
        $settings = [
            ExceptionTraceHelper::SHRINK_PATHS => true
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('shrinkPaths')
            ->with(true);

        $this->helper->getPreviousExceptionTraceBuilderForSettings($settings);
    }

    public function test_RemoveExceptionFileExtensionSetToTrue_getPreviousExceptionTraceBuilderForSettings_ShouldRemoveExtension()
    {
        $settings = [
            ExceptionTraceHelper::REMOVE_EXTENSION => true
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('removeExtension')
            ->with(true);

        $this->helper->getPreviousExceptionTraceBuilderForSettings($settings);
    }

    public function test_ShrinkExceptionNamespacesSetToTrue_getPreviousExceptionTraceBuilderForSettings_ShouldShrinkNamespaces()
    {
        $settings = [
            ExceptionTraceHelper::SHRINK_NAMESPACES => true
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::once())
            ->method('shrinkNamespaces')
            ->with(true);

        $this->helper->getPreviousExceptionTraceBuilderForSettings($settings);
    }

    public function test_ShowExceptionTopLines_getPreviousExceptionTraceBuilderForSettings_ShouldSetShowTopLines()
    {
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

    public function test_LowerThanOneTopLines_getPreviousExceptionTraceBuilderForSettings_ShouldNotSetShowTopLines()
    {
        $settings = [
            ExceptionTraceHelper::SHOW_PREVIOUS_EXCEPTION_TOP_LINES => self::LOWER_THAN_ONE
        ];
        $traceBuilder = $this->givenTraceBuilder();
        $traceBuilder
            ->expects(self::never())
            ->method('showTopLines');

        $this->helper->getPreviousExceptionTraceBuilderForSettings($settings);
    }

    public function test_ShowExceptionBottomLines_getPreviousExceptionTraceBuilderForSettings_ShouldSetShowBottomLines()
    {
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

    public function test_LowerThanOneBottomLines_getPreviousExceptionTraceBuilderForSettings_ShouldNotSetShowBottomLines(
    )
    {
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
     * @return MockObject
     */
    private function givenTraceBuilder()
    {
        $traceBuilder = $this->createMock(TraceBuilder::class);
        $this->factory
            ->method('createExceptionTraceBuilder')
            ->willReturn($traceBuilder);
        return $traceBuilder;
    }
}
