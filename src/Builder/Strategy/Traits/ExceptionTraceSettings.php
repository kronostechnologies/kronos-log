<?php

namespace Kronos\Log\Builder\Strategy\Traits;

trait ExceptionTraceSettings
{
    /**
     * @param \Kronos\Log\WriterInterface $writer
     * @param array $settings Writer settings
     */
    protected function setExceptionTraceSettings(\Kronos\Log\WriterInterface $writer, array $settings)
    {
        if($this->getSettingOrNull($settings, 'includeExceptionArgs')) {
            $writer->setIncludeExceptionArgs(true);
        }

        if(($count = $this->getSettingOrNull($settings, 'showExceptionTopLines')) !== null) {
            $writer->setShowExceptionTopLines($count);
        }

        if(($count = $this->getSettingOrNull($settings, 'showExceptionBottomLines')) !== null) {
            $writer->setShowExceptionBottomLines($count);
        }

        if($this->getSettingOrNull($settings, 'showPreviousException')) {
            $writer->setShowPreviousException(true);

            if(($count = $this->getSettingOrNull($settings, 'showPreviousExceptionTopLines')) !== null) {
                $writer->setShowPreviousExceptionTopLines($count);
            }

            if(($count = $this->getSettingOrNull($settings, 'showPreviousExceptionBottomLines')) !== null) {
                $writer->setShowPreviousExceptionBottomLines($count);
            }
        }
    }

    private function getSettingOrNull($settings, $key) {
        return (isset($settings[$key]) ? $settings[$key] : null);
    }
}