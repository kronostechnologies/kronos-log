<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Exception\InvalidCustomWriter;
use Kronos\Log\Builder\Strategy;

class CustomWriter
{
    public function getStrategyForClassname($classname) {
        if(class_exists($classname)) {
            $reflection = new \ReflectionClass($classname);
            if($reflection->implementsInterface(Strategy::class)) {
                return $reflection->newInstance();
            }
            else {
                throw new InvalidCustomWriter("$classname must implement ".Strategy::class);
            }
        }
        else {
            throw new InvalidCustomWriter("$classname class does not exists");
        }
    }
}