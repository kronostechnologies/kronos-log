<?php

namespace Kronos\Log\Traits;

trait PrependDateTime
{
    private bool $prependDatetime = false;

    public function setPrependDateTime(bool $prependDatetime = true): void
    {
        $this->prependDatetime = $prependDatetime;
    }

    public function prependDateTime($message)
    {
        if ($this->prependDatetime) {
            return '[' . date('Y-m-d H:i:s') . ']' . $message;
        } else {
            return $message;
        }
    }
}
