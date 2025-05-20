<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Factory\Formatter;

class ExceptionTraceHelper
{
    const INCLUDE_ARGS = 'includeExceptionArgs';
    const STRIP_BASE_PATH = 'stripExceptionBasePath';
    const SHRINK_PATHS = 'shrinkExceptionPaths';
    const REMOVE_EXTENSION = 'removeExceptionFileExtension';
    const SHRINK_NAMESPACES = 'shrinkExceptionNamespaces';

    const SHOW_EXCEPTION_STACKTRACE = 'showExceptionStackTrace';
    const SHOW_EXCEPTION_TOP_LINES = 'showExceptionTopLines';
    const SHOW_EXCEPTION_BOTTOM_LINES = 'showExceptionBottomLines';

    const SHOW_PREVIOUS_EXCEPTION_STACKTRACE = 'showPreviousExceptionStackTrace';
    const SHOW_PREVIOUS_EXCEPTION_TOP_LINES = 'showPreviousExceptionTopLines';
    const SHOW_PREVIOUS_EXCEPTION_BOTTOM_LINES = 'showPreviousExceptionBottomLines';

    private Formatter $factory;

    public function __construct(?Formatter $factory = null)
    {
        $this->factory = $factory ?: new Formatter();
    }

    /**
     * @param $settings
     * @return \Kronos\Log\Formatter\Exception\TraceBuilder|null
     */
    public function getExceptionTraceBuilderForSettings($settings)
    {
        if ($this->showExceptionStrackTrace($settings)) {
            $traceBuilder = $this->factory->createExceptionTraceBuilder();

            if ($this->getSettingOrNull($settings, self::INCLUDE_ARGS)) {
                $traceBuilder->includeArgs();
            }

            $stripBasePath = $this->getSettingOrNull($settings, self::STRIP_BASE_PATH);
            if ($stripBasePath) {
                $traceBuilder->stripBasePath($stripBasePath);
            }

            if ($this->getSettingOrNull($settings, self::SHRINK_PATHS)) {
                $traceBuilder->shrinkPaths(true);
            }

            if ($this->getSettingOrNull($settings, self::REMOVE_EXTENSION)) {
                $traceBuilder->removeExtension(true);
            }

            if ($this->getSettingOrNull($settings, self::SHRINK_NAMESPACES)) {
                $traceBuilder->shrinkNamespaces(true);
            }

            $count = $this->getSettingOrNull($settings, self::SHOW_EXCEPTION_TOP_LINES);
            if ($count >= 1) {
                $traceBuilder->showTopLines($count);
            }

            $count = $this->getSettingOrNull($settings, self::SHOW_EXCEPTION_BOTTOM_LINES);
            if ($count >= 1) {
                $traceBuilder->showBottomLines($count);
            }

            return $traceBuilder;
        }

        return null;
    }

    public function getPreviousExceptionTraceBuilderForSettings($settings)
    {
        if ($this->showPreviousExceptionStrackTrace($settings)) {
            $traceBuilder = $this->factory->createExceptionTraceBuilder();

            if ($this->getSettingOrNull($settings, self::INCLUDE_ARGS)) {
                $traceBuilder->includeArgs();
            }

            $stripBasePath = $this->getSettingOrNull($settings, self::STRIP_BASE_PATH);
            if ($stripBasePath) {
                $traceBuilder->stripBasePath($stripBasePath);
            }

            if ($this->getSettingOrNull($settings, self::SHRINK_PATHS)) {
                $traceBuilder->shrinkPaths(true);
            }

            if ($this->getSettingOrNull($settings, self::REMOVE_EXTENSION)) {
                $traceBuilder->removeExtension(true);
            }

            if ($this->getSettingOrNull($settings, self::SHRINK_NAMESPACES)) {
                $traceBuilder->shrinkNamespaces(true);
            }

            $count = $this->getSettingOrNull($settings, self::SHOW_PREVIOUS_EXCEPTION_TOP_LINES);
            if ($count >= 1) {
                $traceBuilder->showTopLines($count);
            }

            $count = $this->getSettingOrNull($settings, self::SHOW_PREVIOUS_EXCEPTION_BOTTOM_LINES);
            if ($count >= 1) {
                $traceBuilder->showBottomLines($count);
            }

            return $traceBuilder;
        } else {
            return null;
        }
    }

    /**
     * @param $settings
     * @return bool
     */
    private function showExceptionStrackTrace($settings)
    {
        return !array_key_exists(self::SHOW_EXCEPTION_STACKTRACE,
                $settings) || $settings[self::SHOW_EXCEPTION_STACKTRACE];
    }

    /**
     * @param $settings
     * @return bool
     */
    private function showPreviousExceptionStrackTrace($settings)
    {
        return !array_key_exists(self::SHOW_PREVIOUS_EXCEPTION_STACKTRACE,
                $settings) || $settings[self::SHOW_PREVIOUS_EXCEPTION_STACKTRACE];
    }

    private function getSettingOrNull($settings, $key)
    {
        return (isset($settings[$key]) ? $settings[$key] : null);
    }
}
