<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Exception\InvalidCustomWriter;
use Kronos\Log\Builder\Strategy;

class CustomWriterStrategy
{
    /**
     * @param class-string $classname
     * @throws InvalidCustomWriter
     */
    public function getStrategyForClassname(string $classname): Strategy
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
