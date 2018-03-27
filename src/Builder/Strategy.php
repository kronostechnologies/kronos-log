<?php

namespace Kronos\Log\Builder;

use Kronos\Log\WriterInterface;

interface Strategy
{

    /**
     * @param array $settings
     * @return WriterInterface
     */
    public function buildFromArray(array $settings);
}