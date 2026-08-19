<?php

namespace Kronos\Log;

use Kronos\Log\Builder\Strategy\StrategySelector;
use Kronos\Log\Enumeration\WriterTypes;
use Kronos\Log\Exception\NoWriter;
use Kronos\Log\Factory\LoggerFactory as LoggerFactory;
use phpDocumentor\Reflection\Types\Context;

class Builder
{
    private LoggerFactory $loggerFactory;
    private StrategySelector $selector;

    public function __construct(?LoggerFactory $loggerFactory = null, ?StrategySelector $selector = null)
    {
        $this->loggerFactory = $loggerFactory ?: new LoggerFactory();
        $this->selector = $selector ?: new StrategySelector();
    }


    /**
     * @param array $settings
     * @return Logger
     * @throws NoWriter
     */
    public function buildFromArray(array $settings)
    {
        $logger = $this->loggerFactory->createLogger();

        if (empty($settings)) {
            throw new NoWriter('Logger should have at least one writer');
        }

        foreach ($settings as $writerSetting) {
            $strategy = $this->selector->getStrategyForType($writerSetting['type'] ?? null);
            $writer = $strategy->buildFromArray($writerSetting['settings']);
            $logger->addWriter($writer);
        }

        return $logger;
    }
}
