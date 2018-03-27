<?php

namespace Kronos\Tests\Log\Writer;

use Kronos\Log\Writer\Memory;
use Psr\Log\LogLevel;

class MemoryTest extends \PHPUnit_Framework_TestCase
{

    const INFO_LOG_LEVEL = LogLevel::INFO;
    const A_MESSAGE = 'a message {key}';
    const CONTEXT_KEY = 'key';
    const CONTEXT_VALUE = 'value';
    const INTERPOLATED_MESSAGE_WITH_LOG_LEVEL = 'INFO : a message value';


    /**
     * @var Kronos\Log\Writer\Memory
     */
    private $writer;

    public function setUp()
    {

        $this->writer = new Memory();
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