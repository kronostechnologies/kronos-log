<?php

namespace Kronos\Tests\Log\Formatter;

use Kronos\Log\Formatter\ContextStringifier;

class ContextStringifierTest extends \PHPUnit_Framework_TestCase
{

    const KEY = 'key';
    const VALUE = 'value';
    const A_STRING = 'a string';

    const FIRST_KEY = 'first key';
    const FIRST_VALUE = 'first value';
    const SECOND_KEY = 'second key';
    const SECOND_VALUE = 'second value';

    /**
     * @var \Kronos\Log\Formatter\ContextStringifier
     */
    private $context_stringifier;

    public function setUp()
    {
        $this->context_stringifier = new \Kronos\Log\Formatter\ContextStringifier();
    }

    public function test_EmptyContext_Stringify_ShouldReturnEmptyString()
    {
        $context = [];

        $stringifiedContext = $this->context_stringifier->stringify($context);

        $this->assertSame('', $stringifiedContext);
    }

    public function test_ContextWithValue_Stringify_ShouldReturnKeyAndValue()
    {
        $context = [self::KEY => self::VALUE];

        $stringifiedContext = $this->context_stringifier->stringify($context);

        $this->assertEquals(self::KEY . ': ' . self::VALUE, $stringifiedContext);
    }

    public function test_ContextWithArrayValue_Stringify_ShouldReturnPrintedArray()
    {
        $array_value = [1, 2, 3];
        $context = [self::KEY => $array_value];

        $stringifiedContext = $this->context_stringifier->stringify($context);

        $this->assertEquals(self::KEY . ': ' . print_r($array_value, true), $stringifiedContext);
    }

    public function test_ContextWithObjectWithoutToString_Stringify_ShouldReturnPrintedObject()
    {
        $object = new ObjectWithoutToString();
        $object->property = self::A_STRING;
        $context = [self::KEY => $object];

        $stringifiedContext = $this->context_stringifier->stringify($context);

        $this->assertEquals(self::KEY . ': ' . print_r($object, true), $stringifiedContext);
    }

    public function test_ContextWithObjectWithToString_Stringify_ShouldReturnObjectAsString()
    {
        $object = new ObjectWithToString();
        $object->property = self::A_STRING;
        $context = [self::KEY => $object];

        $stringifiedContext = $this->context_stringifier->stringify($context);

        $this->assertEquals(self::KEY . ': ' . $object->__toString(), $stringifiedContext);
    }

    public function test_ContextWithMoreThanOneKey_Stringify_ShouldReturnBothValuesJoinedByEOL()
    {
        $context = [
            self::FIRST_KEY => self::FIRST_VALUE,
            self::SECOND_KEY => self::SECOND_VALUE
        ];

        $stringifiedContext = $this->context_stringifier->stringify($context);

        $expected_string = self::FIRST_KEY . ': ' . self::FIRST_VALUE . PHP_EOL . self::SECOND_KEY . ': ' . self::SECOND_VALUE;
        $this->assertEquals($expected_string, $stringifiedContext);
    }

    public function test_ExcludeKey_Stringify_ShouldNotStringifyContextKey()
    {
        $context = [
            self::FIRST_KEY => self::FIRST_VALUE,
            self::SECOND_KEY => self::SECOND_VALUE
        ];
        $this->context_stringifier->excludeKey(self::FIRST_KEY);

        $stringifiedContext = $this->context_stringifier->stringify($context);

        $expected_string = self::SECOND_KEY . ': ' . self::SECOND_VALUE;
        $this->assertEquals($expected_string, $stringifiedContext);
    }

    public function test_EmptyContext_stringifyArray_ShouldReturnEmptyArray()
    {
        $context = [];

        $stringifiedContext = $this->context_stringifier->stringifyArray($context);

        $this->assertSame([], $stringifiedContext);
    }

    public function test_IntegerValue_stringifyArray_ShouldReturnStringValue()
    {
        $context = ['integer' => 123];

        $stringifiedContext = $this->context_stringifier->stringifyArray($context);

        $this->assertEquals('123', $stringifiedContext['integer']);
        $this->assertInternalType('string', $stringifiedContext['integer']);
    }

    public function test_ObjectWithToStringValue_stringifyArray_ShouldReturnStringValue()
    {
        $object = new ObjectWithToString();
        $object->property = 'property';
        $context = ['object' => $object];

        $stringifiedContext = $this->context_stringifier->stringifyArray($context);

        $this->assertEquals($object->__toString(), $stringifiedContext['object']);
    }

    public function test_ObjectWithoutToStringValue_stringifyArray_ShouldReturnStringValue()
    {
        $object = new ObjectWithoutToString();
        $object->property = 'property';
        $context = ['object' => $object];

        $stringifiedContext = $this->context_stringifier->stringifyArray($context);

        $this->assertEquals(print_r($object, true), $stringifiedContext['object']);
    }

    public function test_ArrayValue_stringifyArray_ShouldStringifyArrayValues()
    {
        $objectWithToString = new ObjectWithToString();
        $objectWithToString->property = 'property';
        $objectWithoutToString = new ObjectWithoutToString();
        $objectWithoutToString->property = 'property';
        $array = [
            'objectWithToString' => $objectWithToString,
            'objectWithoutToString' => $objectWithoutToString,
            'integer' => 123,
            'string' => 'string',
            'nested array' => [
                'nested objectWithToString' => $objectWithToString,
                'nested objectWithoutToString' => $objectWithoutToString,
                'nested integer' => 123,
                'nested string' => 'string'
            ]
        ];
        $context = ['array' => $array];

        $stringifiedContext = $this->context_stringifier->stringifyArray($context);

        $this->assertInternalType('array', $stringifiedContext['array']);
        $this->assertInternalType('string', $stringifiedContext['array']['integer']);
        $this->assertEquals('123', $stringifiedContext['array']['integer']);
        $this->assertEquals('string', $stringifiedContext['array']['string']);
        $this->assertEquals($objectWithToString->__toString(), $stringifiedContext['array']['objectWithToString']);
        $this->assertEquals(print_r($objectWithoutToString, true),
            $stringifiedContext['array']['objectWithoutToString']);
        $this->assertInternalType('array', $stringifiedContext['array']['nested array']); // Testing recursion
        $this->assertInternalType('string', $stringifiedContext['array']['nested array']['nested integer']);
        $this->assertEquals('123', $stringifiedContext['array']['nested array']['nested integer']);
        $this->assertEquals('string', $stringifiedContext['array']['nested array']['nested string']);
        $this->assertEquals($objectWithToString->__toString(),
            $stringifiedContext['array']['nested array']['nested objectWithToString']);
        $this->assertEquals(print_r($objectWithoutToString, true),
            $stringifiedContext['array']['nested array']['nested objectWithoutToString']);
    }

    public function test_EmptyNestedArray_stringifyArray_ShouldRemoveEmptyArray()
    {
        $context = [
            'nested empty array' => []
        ];

        $stringifiedContext = $this->context_stringifier->stringifyArray($context);

        $this->assertEquals([], $stringifiedContext);
    }

    public function test_ExcludeKey_stringifyArray_ShouldRecursivelyRemoveMatchingKeys()
    {
        $context = [
            'key' => 'value',
            'excludedKey' => 'value',
            'array' => [
                'key' => 'value',
                'excludedKey' => 'value',
                'array with only excluded key' => [
                    'excludedKey' => 'value'
                ]
            ]
        ];
        $expectedArray = [
            'key' => 'value',
            'array' => [
                'key' => 'value',
            ]
        ];
        $this->context_stringifier->excludeKey('excludedKey');

        $stringifiedContext = $this->context_stringifier->stringifyArray($context);

        $this->assertEquals($expectedArray, $stringifiedContext);
    }
}

class ObjectWithoutToString
{
    public $property;
}

class ObjectWithToString
{

    /**
     * @var string
     */
    public $property;

    public function __toString()
    {
        return $this->property;
    }
}
