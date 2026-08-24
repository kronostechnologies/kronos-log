<?php

namespace Kronos\Log\Traits;

use Stringable;

trait PrependDateTime
{
    private bool $prependDatetime = false;

    public function setPrependDateTime(bool $prependDatetime = true): void
    {
        $this->prependDatetime = $prependDatetime;
    }

    public function prependDateTime(string | Stringable $message): string
    {
        if ($this->prependDatetime) {
            return '[' . date('Y-m-d H:i:s') . ']' . (string)$message;
        } else {
            return (string)$message;
        }
    }
}
