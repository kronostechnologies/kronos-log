<?php

namespace Kronos\Log\Formatter\Exception;

class Factory
{
    public function createNamespaceShrinker(): NamespaceShrinker
    {
        return new NamespaceShrinker();
    }

    public function createLineAssembler(?NamespaceShrinker $namespaceShrinker = null): LineAssembler
    {
        return new LineAssembler($namespaceShrinker);
    }
}
