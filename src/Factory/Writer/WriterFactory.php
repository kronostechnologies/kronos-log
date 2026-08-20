<?php

namespace Kronos\Log\Factory\Writer;

use Kronos\Log\WriterInterface;

interface WriterFactory
{
    public function createFromArray(array $settings): WriterInterface;
}
