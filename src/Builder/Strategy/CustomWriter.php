<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Exception\InvalidCustomWriter;
use Kronos\Log\Builder\Strategy;

class CustomWriter
{
    /**
     * @param $classname
     * @return Strategy
     * @throws InvalidCustomWriter
     * @throws \ReflectionException
     */
    public function getStrategyForClassname($classname)
    {
        if (class_exists($classname)) {
            $reflection = new \ReflectionClass($classname);
            if ($reflection->implementsInterface(Strategy::class)) {
                $instance = $reflection->newInstance();
                /** @var Strategy $instance */
                return $instance;
            }

            throw new InvalidCustomWriter("$classname must implement " . Strategy::class);
        }

        throw new InvalidCustomWriter("$classname class does not exists");
    }
}
