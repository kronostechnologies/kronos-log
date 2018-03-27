<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy;
use Kronos\Log\Factory\Writer As WriterFactory;

class Console extends AbstractWriter
{

    const FORCE_ANSI_COLOR = 'forceAnsiColor';
    const FORCE_NO_ANSI_COLOR = 'forceNoAnsiColor';

    /**
     * @var WriterFactory
     */
    private $factory;

    public function __construct(WriterFactory $factory = null)
    {
        $this->factory = is_null($factory) ? new WriterFactory() : $factory;
    }

    /**
     * @param array $settings
     * @return \Kronos\Log\Writer\Console
     */
    public function buildFromArray(array $settings)
    {
        $writer = $this->factory->createConsoleWriter();

        $this->setCommonSettings($writer, $settings);

        if (isset($settings['forceAnsiColor']) && $settings['forceAnsiColor']) {
            $writer->setForceAnsiColorSupport();
        }
        if (isset($settings['forceNoAnsiColor']) && $settings['forceNoAnsiColor']) {
            $writer->setForceNoAnsiColorSupport();
        }

        return $writer;
    }
}