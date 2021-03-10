<?php

namespace Kronos\Log\Adaptor;

class FileFactory
{
    public function createFileAdaptor($filename): File
    {
        return new File($filename);
    }

    public function createTTYAdaptor($filename): TTY
    {
        return new TTY($filename);
    }
}
