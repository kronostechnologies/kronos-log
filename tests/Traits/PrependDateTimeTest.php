<?php

namespace Kronos\Tests\Log\Traits;

use Kronos\Log\Traits\PrependDateTime;
use PHPUnit\Framework\TestCase;

class PrependDateTimeTest extends TestCase
{
    use PrependDateTime;
    const A_MESSAGE = ' a message';
    const DATETIME_REGEX = '\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]';

    public function test_NewPrepender_PrependDateTime_ShouldReturnGivenMessage()
    {
        $returned_message = $this->prependDateTime(self::A_MESSAGE);

        $this->assertSame(self::A_MESSAGE, $returned_message);
    }

    public function test_PrependerSetPrependDateTime_PrependDateTime_ShouldReturnGivenMessagePrependedWithTime()
    {
        $this->setPrependDateTime();

        $returned_message = $this->prependDateTime(self::A_MESSAGE);

        $this->assertMatchesRegularExpression('/' . self::DATETIME_REGEX . '' . self::A_MESSAGE . '/', $returned_message);
    }
}
