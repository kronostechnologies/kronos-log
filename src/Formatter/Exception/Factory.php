<?php

namespace Kronos\Log\Formatter\Exception;

class Factory
{
    public function createLineAssembler(): LineAssembler
    {
        return new LineAssembler();
    }
}
