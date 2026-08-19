<?php

namespace Kronos\Tests\Log\Traits;

use Kronos\Log\Traits\Interpolate;
use Kronos\Tests\Log\Formatter\ObjectWithoutToString;
use Kronos\Tests\Log\Formatter\ObjectWithToString;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InterpolateTest extends TestCase
{
    use Interpolate;
    const A_MESSAGE = 'Some message {key}';
    const INTERPOLATED_MESSAGE = 'Some message value';
    const KEY = 'key';
    const VALUE = 'value';
    const MESSAGE_WITH_UNDEFINED = 'Some message ~UNDEFINED~';

    #[Test]
    public function messageWithPlaceholder_Interpolate_ShouldReplacePlaceholderWithContextValue()
    {
        $original_message = self::A_MESSAGE;

        $interpolated_message = $this->interpolate($original_message, [self::KEY => self::VALUE]);

        $this->assertEquals(self::INTERPOLATED_MESSAGE, $interpolated_message);
    }

    #[Test]
    public function contextWithoutMessagePlaceholder_Interpolate_ShouldReplacePlaceholderWithUndefined()
    {
        $original_message = self::A_MESSAGE;

        $interpolated_message = $this->interpolate($original_message, []);

        $this->assertEquals(self::MESSAGE_WITH_UNDEFINED, $interpolated_message);
    }

    #[Test]
    public function objectInContext_Interpolate_ShouldReplaceWithUndefined()
    {
        $original_message = self::A_MESSAGE;
        $object = new ObjectWithoutToString();
        $object->property = 'value';

        $interpolated_message = $this->interpolate($original_message, [self::KEY => $object]);

        $this->assertEquals(self::MESSAGE_WITH_UNDEFINED, $interpolated_message);
    }

    #[Test]
    public function objectWithToStringInContext_Interpolate_ShouldReplacePlaceholderWithToStringResult()
    {
        $original_message = self::A_MESSAGE;
        $object = new ObjectWithToString();
        $object->property = 'value';
        $expectedMessage = str_replace('{' . self::KEY . '}', (string)$object, self::A_MESSAGE);

        $interpolated_message = $this->interpolate($original_message, [self::KEY => $object]);

        $this->assertEquals($expectedMessage, $interpolated_message);
    }

    #[Test]
    public function arrayInContext_Interpolate_ShouldReplaceWithUndefined()
    {
        $original_message = self::A_MESSAGE;
        $array = ['index' => 'value'];

        $interpolated_message = $this->interpolate($original_message, [self::KEY => $array]);

        $this->assertEquals(self::MESSAGE_WITH_UNDEFINED, $interpolated_message);
    }

    #[Test]
    public function zeroInContext_Interpolate_ShouldReplaceWithZero()
    {
        $original_message = self::A_MESSAGE;
        $value = 0;
        $expectedMessage = str_replace('{' . self::KEY . '}', $value, self::A_MESSAGE);

        $interpolated_message = $this->interpolate($original_message, [self::KEY => $value]);

        $this->assertEquals($expectedMessage, $interpolated_message);
    }
}
