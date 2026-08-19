<?php

namespace Kronos\Tests\Log\Writer;

use Kronos\Log\Writer\MemoryWriter;
use Psr\Log\LogLevel;

class MemoryWriterTest extends \PHPUnit\Framework\TestCase
{
    const string INFO_LOG_LEVEL = LogLevel::INFO;
    const string A_MESSAGE = 'a message {key}';
    const string CONTEXT_KEY = 'key';
    const string CONTEXT_VALUE = 'value';
    const string INTERPOLATED_MESSAGE_WITH_LOG_LEVEL = 'INFO : a message value';

    private MemoryWriter $writer;

    public function setUp(): void
    {

        $this->writer = new MemoryWriter();
    }

    public function test_Writer_Log_WillAddInterpolatedMessageWithLogLevelToContent()
    {

        $this->writer->log(self::INFO_LOG_LEVEL, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);

        $this->assertContains(self::INTERPOLATED_MESSAGE_WITH_LOG_LEVEL, $this->writer->getLogs());
    }

    public function test_Writer_LogTwice_WillAddTwiceToLogs()
    {

        $this->writer->log(self::INFO_LOG_LEVEL, self::A_MESSAGE);
        $this->writer->log(self::INFO_LOG_LEVEL, self::A_MESSAGE);

        $this->assertCount(2, $this->writer->getLogs());
    }
}
