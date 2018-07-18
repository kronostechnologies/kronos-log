<?php

namespace Kronos\Tests\Log\Formatter;

class ObjectWithToString
{

    /**
     * @var string
     */
    public $property;

    public function __toString()
    {
        return $this->property;
    }
}
