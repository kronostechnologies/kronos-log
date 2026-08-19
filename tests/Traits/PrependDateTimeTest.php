<?php

namespace Kronos\Tests\Log\Traits;

use Kronos\Log\Traits\PrependDateTime;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PrependDateTimeTest extends TestCase
{
    use PrependDateTime;
    const string A_MESSAGE = ' a message';
    const string DATETIME_REGEX = '\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]';

    #[Test]
    public function newPrepender_PrependDateTime_ShouldReturnGivenMessage()
    {
        $returned_message = $this->prependDateTime(self::A_MESSAGE);

        $this->assertSame(self::A_MESSAGE, $returned_message);
    }

    #[Test]
    public function prependerSetPrependDateTime_PrependDateTime_ShouldReturnGivenMessagePrependedWithTime()
    {
        $this->setPrependDateTime();

        $returned_message = $this->prependDateTime(self::A_MESSAGE);

        $this->assertMatchesRegularExpression('/' . self::DATETIME_REGEX . '' . self::A_MESSAGE . '/', $returned_message);
    }
}
