<?php

namespace Kronos\Log\Factory\Writer;

use Kronos\Log\Writer\WriterInterface;

interface WriterFactory
{
    public function createFromArray(array $settings): WriterInterface;
}
