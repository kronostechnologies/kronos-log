<?php

namespace Kronos\Tests\Log\Traits;

use Kronos\Log\Traits\PrependContext;
use PHPUnit\Framework\TestCase;

class PrependContextTest extends TestCase
{
    use PrependContext;

    const A_MESSAGE = 'a message';
    const CONTEXT_KEY = 'key';
    const CONTEXT_VALUE = 'value';
    const ANOTHER_CONTEXT_KEY = 'another key';
    const FIRST_KEY = 'first key';
    const FIRST_VALUE = 'first_value';
    const SECOND_KEY = 'second key';
    const SECOND_VALUE = 'second value';

    private $context_prepender;

    public function test_NewPrependContext_PrependContext_ShouldReturnMessageUnchanged()
    {
        $returned_message = $this->prependContext(self::A_MESSAGE, []);

        $this->assertEquals(self::A_MESSAGE, $returned_message);
    }

    public function test_ContextKeyToPrepend_PrependContext_ShouldReturnMessagePrependedWithValue()
    {
        $context = [
            self::CONTEXT_KEY => self::CONTEXT_VALUE
        ];
        $this->addContextKeyToPrepend(self::CONTEXT_KEY);

        $returned_message = $this->prependContext(self::A_MESSAGE, $context);

        $this->assertEquals(self::CONTEXT_VALUE . ' ' . self::A_MESSAGE, $returned_message);
    }

    public function test_UnknownContextKeyToPrepend_PrependContext_ShouldReturnMessageUnchanged()
    {
        $context = [
            self::CONTEXT_KEY => self::CONTEXT_VALUE
        ];
        $this->addContextKeyToPrepend(self::ANOTHER_CONTEXT_KEY);

        $returned_message = $this->prependContext(self::A_MESSAGE, $context);

        $this->assertEquals(self::A_MESSAGE, $returned_message);
    }

    public function test_MoreThanOneKeyToPrepend_PrependContext_ShouldPrependThemInOrder()
    {
        $context = [
            self::FIRST_KEY => self::FIRST_VALUE,
            self::SECOND_KEY => self::SECOND_VALUE
        ];
        $this->addContextKeyToPrepend(self::FIRST_KEY);
        $this->addContextKeyToPrepend(self::SECOND_KEY);

        $returned_message = $this->prependContext(self::A_MESSAGE, $context);

        $expected_message = self::FIRST_VALUE . ' ' . self::SECOND_VALUE . ' ' . self::A_MESSAGE;
        $this->assertEquals($expected_message, $returned_message);
    }

}
