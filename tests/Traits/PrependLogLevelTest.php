<?php

namespace Kronos\Tests\Log\Traits;

use Kronos\Log\Traits\PrependLogLevel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class PrependLogLevelTest extends TestCase
{
    use PrependLogLevel;

    const A_MESSAGE = 'a message';
    const ANY_LOG_LEVEL = LogLevel::INFO;

    #[Test]
    public function newPrepender_PrependLogLevel_ShouldReturnGivenMessage()
    {
        $returned_message = $this->prependLogLevel(self::ANY_LOG_LEVEL, self::A_MESSAGE);

        $this->assertEquals(self::A_MESSAGE, $returned_message);
    }

    #[Test]
    public function prependerSetPrependLogLevel_PrependLogLevel_ShouldPrependMessageWithLogLevelInUpperCase()
    {
        $this->setPrependLogLevel();

        $returned_message = $this->prependLogLevel(self::ANY_LOG_LEVEL, self::A_MESSAGE);

        $this->assertEquals(strtoupper(self::ANY_LOG_LEVEL) . ' : ' . self::A_MESSAGE, $returned_message);
    }
}
