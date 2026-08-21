<?php

namespace Kronos\Log\Traits;

use Stringable;

trait PrependContext
{

    private array $prepended_keys = [];

    public function addContextKeyToPrepend(string $key): void
    {
        $this->prepended_keys[] = $key;
    }

    public function prependContext(string | Stringable $message, array $context): string
    {
        foreach (array_reverse($this->prepended_keys) as $key) {
            if (isset($context[$key])) {
                $message = (string)$context[$key] . ' ' . (string)$message;
            }
        }

        return (string)$message;
    }
}
