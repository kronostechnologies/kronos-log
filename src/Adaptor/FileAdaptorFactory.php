<?php

namespace Kronos\Log\Adaptor;

class FileAdaptorFactory
{
    public function createFileAdaptor($filename): FileAdaptor
    {
        return new FileAdaptor($filename);
    }

    public function createTTYAdaptor($filename): TTYAdaptor
    {
        return new TTYAdaptor($filename);
    }
}
