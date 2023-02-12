<?php

namespace Kronos\Log\Formatter\Exception;

class NamespaceShrinker
{
    private const NAMESPACE_SEPARATOR = '\\';
    private const UNDERSCORE_SEPARATOR = '_';

    public function shrink(string $fqn): string
    {
        if (strpos($fqn, self::NAMESPACE_SEPARATOR) !== false) {
            return $this->shrinkUsingSeparator($fqn, self::NAMESPACE_SEPARATOR);
        } elseif (strpos($fqn, self::UNDERSCORE_SEPARATOR) !== false) {
            return $this->shrinkUsingSeparator($fqn, self::UNDERSCORE_SEPARATOR);
        } else {
            return $fqn;
        }
    }

    /**
     * @param non-empty-string $separator
     */
    public function shrinkUsingSeparator(string $fqn, string $separator): string
    {
        $parts = explode($separator, $fqn);
        for ($i = 0; $i < count($parts) - 2; $i++) {
            $parts[$i] = substr($parts[$i], 0, 1);
        }
        return implode($separator, $parts);
    }
}
