<?php

namespace Kronos\Tests\Log\Builder\Strategy\Traits;

use Kronos\Log\Builder\Strategy;
use \Kronos\Log\Builder\Strategy\Traits\ExceptionTraceSettings;
use Kronos\Log\WriterInterface;

class ExceptionTraceSettingsTest extends \PHPUnit_Framework_TestCase
{
    const TOP_LINES_COUNT = 2;

    private $trait;

    const BOTTOM_LINES_COUNT = 1;

    public function setUp() {
        $this->trait = new TestableTrait();
        $this->trait->writer = $this->getMockWithoutInvokingTheOriginalConstructor(WriterWithTrait::class);
    }

    public function test_NoSettings_setExceptionTraceSettings_ShouldDoNothingToWriter()
    {
        $this->trait->writer
            ->expects(self::never())
            ->method(self::anything());

        $this->trait->buildFromArray([]);
    }

    public function test_includeExceptionArgs_setExceptionTraceSettings_ShouldSetIncludeExceptionArgs()
    {
        $this->trait->writer
            ->expects(self::once())
            ->method('setIncludeExceptionArgs');
        $settings = [
            'includeExceptionArgs' => true
        ];

       $this->trait->buildFromArray($settings);
    }

    public function test_showExceptionTopLines_setExceptionTraceSettings_ShouldSetShowExceptionTopLines()
    {
        $this->trait->writer
            ->expects(self::once())
            ->method('setShowExceptionTopLines')
            ->with(self::TOP_LINES_COUNT);
        $settings = [
            'showExceptionTopLines' => self::TOP_LINES_COUNT
        ];

        $this->trait->buildFromArray($settings);
    }

    public function test_showExceptionBottomLines_setExceptionTraceSettings_ShouldSetShowExceptionBottomLines()
    {
        $this->trait->writer
            ->expects(self::once())
            ->method('setShowExceptionBottomLines')
            ->with(self::BOTTOM_LINES_COUNT);
        $settings = [
            'showExceptionBottomLines' => self::BOTTOM_LINES_COUNT
        ];

        $this->trait->buildFromArray($settings);
    }

    public function test_showPreviousException_setExceptionTraceSettings_ShouldSetShowPreviousException()
    {
        $this->trait->writer
            ->expects(self::once())
            ->method('setShowPreviousException')
            ->with(true);
        $settings = [
            'showPreviousException' => true
        ];

        $this->trait->buildFromArray($settings);
    }

    public function test_showPreviousExceptionAndShowPreviousExceptionTopLines_setExceptionTraceSettings_ShouldSetShowPreviousExceptionTopLines()
    {
        $this->trait->writer
            ->expects(self::once())
            ->method('setShowPreviousExceptionTopLines')
            ->with(self::TOP_LINES_COUNT);
        $settings = [
            'showPreviousException' => true,
            'showPreviousExceptionTopLines' => self::TOP_LINES_COUNT
        ];

        $this->trait->buildFromArray($settings);
    }

    public function test_showPreviousExceptionTopLinesWithoutShowPreviousException_setExceptionTraceSettings_ShouldDoNothing()
    {
        $this->trait->writer
            ->expects(self::never())
            ->method('setShowPreviousExceptionTopLines');
        $settings = [
            'showPreviousExceptionTopLines' => self::TOP_LINES_COUNT
        ];

        $this->trait->buildFromArray($settings);
    }

    public function test_showPreviousExceptionAndShowPreviousExceptionBottomLines_setExceptionTraceSettings_ShouldSetShowPreviousExceptionBottomLines()
    {
        $this->trait->writer
            ->expects(self::once())
            ->method('setShowPreviousExceptionBottomLines')
            ->with(self::BOTTOM_LINES_COUNT);
        $settings = [
            'showPreviousException' => true,
            'showPreviousExceptionBottomLines' => self::BOTTOM_LINES_COUNT
        ];

        $this->trait->buildFromArray($settings);
    }

    public function test_showPreviousExceptionBottomLinesWithoutShowPreviousException_setExceptionTraceSettings_ShouldDoNothing()
    {
        $this->trait->writer
            ->expects(self::never())
            ->method('setShowPreviousExceptionBottomLines');
        $settings = [
            'showPreviousExceptionBottomLines' => self::BOTTOM_LINES_COUNT
        ];

        $this->trait->buildFromArray($settings);
    }
}

class TestableTrait implements Strategy {
    use ExceptionTraceSettings;

    public $writer = null;

    public function buildFromArray(array $settings)
    {
        $this->setExceptionTraceSettings($this->writer, $settings);
    }
}

class WriterWithTrait implements WriterInterface
{
    use \Kronos\Log\Traits\ExceptionTraceBuilder;

    public function canLogLevel($level)
    {
        // TODO: Implement canLogLevel() method.
    }

    public function setCanLog($can_log)
    {
        // TODO: Implement setCanLog() method.
    }

    public function canLog()
    {
        // TODO: Implement canLog() method.
    }

    public function log($level, $message, array $context = [])
    {
        // TODO: Implement log() method.
    }
}