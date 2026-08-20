<?php

namespace Kronos\Tests\Log\Factory\Writer;

use Kronos\Log\Factory\FormatterFactory;
use Kronos\Log\Factory\Writer\ExceptionTraceHelper;
use Kronos\Log\Formatter\Exception\TraceBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExceptionTraceHelperTest extends TestCase
{
    const int TOP_LINES = 4;
    const int LOWER_THAN_ONE = -1;
    const int BOTTOM_LINES = 2;
    const string BASE_PATH = '/base/path/';

    private FormatterFactory & MockObject $factory;
    private ExceptionTraceHelper $helper;

    public function setUp(): void
    {
        $this->factory = $this->createMock(FormatterFactory::class);

        $this->helper = new ExceptionTraceHelper($this->factory);
    }

    #[Test]
    public function emptySettings_getExceptionTraceBuilderForSettings_ShouldCreateAndReturnTraceBuilder()
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

    #[Test]
    public function showExceptionStackTraceSettingSetToFalse_getExceptionTraceBuilderForSettings_ShouldNotCreateTraceBuilderAndReturnNull(
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

    #[Test]
    public function includeArgsSetToTrue_getExceptionTraceBuilderForSettings_ShouldSetIncludeArgs()
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

    #[Test]
    public function stripExceptionBasePath_getExceptionTraceBuilderForSettings_ShouldStripBasePath()
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

    #[Test]
    public function shrinkExceptionPathsSetToTrue_getExceptionTraceBuilderForSettings_ShouldShrinkPaths()
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

    #[Test]
    public function removeExceptionFileExtensionSetToTrue_getExceptionTraceBuilderForSettings_ShouldRemoveExtension()
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

    #[Test]
    public function shrinkExceptionNamespacesSetToTrue_getExceptionTraceBuilderForSettings_ShouldShrinkNamespaces()
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

    #[Test]
    public function showExceptionTopLines_getExceptionTraceBuilderForSettings_ShouldSetShowTopLines()
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

    #[Test]
    public function lowerThanOneTopLines_getExceptionTraceBuilderForSettings_ShouldNotSetShowTopLines()
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

    #[Test]
    public function showExceptionBottomLines_getExceptionTraceBuilderForSettings_ShouldSetShowBottomLines()
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

    #[Test]
    public function lowerThanOneBottomLines_getExceptionTraceBuilderForSettings_ShouldNotSetShowBottomLines()
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

    #[Test]
    public function emptySettings_getPreviousExceptionTraceBuilderForSettings_ShouldCreateAndReturnTraceBuilder()
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

    #[Test]
    public function showExceptionStackTraceSettingSetToFalse_getPreviousExceptionTraceBuilderForSettings_ShouldNotCreateTraceBuilderAndReturnNull(
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

    #[Test]
    public function includeArgsSetToTrue_getPreviousExceptionTraceBuilderForSettings_ShouldSetIncludeArgs()
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

    #[Test]
    public function stripExceptionBasePath_getPreviousExceptionTraceBuilderForSettings_ShouldStripBasePath()
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

    #[Test]
    public function shrinkExceptionPathsSetToTrue_getPreviousExceptionTraceBuilderForSettings_ShouldShrinkPaths()
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

    #[Test]
    public function removeExceptionFileExtensionSetToTrue_getPreviousExceptionTraceBuilderForSettings_ShouldRemoveExtension()
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

    #[Test]
    public function shrinkExceptionNamespacesSetToTrue_getPreviousExceptionTraceBuilderForSettings_ShouldShrinkNamespaces()
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

    #[Test]
    public function showExceptionTopLines_getPreviousExceptionTraceBuilderForSettings_ShouldSetShowTopLines()
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

    #[Test]
    public function lowerThanOneTopLines_getPreviousExceptionTraceBuilderForSettings_ShouldNotSetShowTopLines()
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

    #[Test]
    public function showExceptionBottomLines_getPreviousExceptionTraceBuilderForSettings_ShouldSetShowBottomLines()
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

    #[Test]
    public function lowerThanOneBottomLines_getPreviousExceptionTraceBuilderForSettings_ShouldNotSetShowBottomLines(
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
