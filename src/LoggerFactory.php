<?php

namespace Kronos\Log;

use Kronos\Log\Exception\NoWriter;
use Kronos\Log\Factory\Writer\WriterFactoryProvider;

class LoggerFactory
{
    private WriterFactoryProvider $selector;

    public function __construct(?WriterFactoryProvider $selector = null)
    {
        $this->selector = $selector ?: new WriterFactoryProvider();
    }

    public function create(): Logger
    {
        return new Logger();
    }

    /**
     * @throws NoWriter
     */
    public function createFromArray(array $settings): Logger
    {
        $logger = $this->create();
        if (empty($settings)) {
            throw new NoWriter('Logger should have at least one writer');
        }

        foreach ($settings as $writerSetting) {
            $factory = $this->selector->getWriterFactoryForType($writerSetting['type'] ?? null);
            $writer = $factory->createFromArray($writerSetting['settings']);
            $logger->addWriter($writer);
        }

        return $logger;
    }
}
