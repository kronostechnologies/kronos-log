<?php

namespace Kronos\Log\Factory\Writer;

use Kronos\Log\Exception\InvalidCustomWriter;

class CustomWriterFactory
{
    /**
     * @param class-string $classname
     * @throws InvalidCustomWriter
     */
    public function getStrategyForClassname(string $classname): WriterFactory
    {
        if (class_exists($classname)) {
            $reflection = new \ReflectionClass($classname);
            if ($reflection->implementsInterface(WriterFactory::class)) {
                $instance = $reflection->newInstance();
                /** @var WriterFactory $instance */
                return $instance;
            }

            throw new InvalidCustomWriter("$classname must implement " . WriterFactory::class);
        }

        throw new InvalidCustomWriter("$classname class does not exists");
    }
}
