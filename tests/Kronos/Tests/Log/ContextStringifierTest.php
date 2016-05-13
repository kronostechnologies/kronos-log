<?php

namespace Kronos\Tests\Log;

use Kronos\Log\ContextStringifier;

class ContextStringifierTest extends \PHPUnit_Framework_TestCase {

	const KEY = 'key';
	const VALUE = 'value';
	const A_STRING = 'a string';

	const FIRST_KEY = 'first key';
	const FIRST_VALUE = 'first value';
	const SECOND_KEY = 'second key';
	const SECOND_VALUE = 'second value';

	/**
	 * @var ContextStringifier
	 */
	private $context_stringifier;

	public function setUp() {
		$this->context_stringifier = new ContextStringifier();
	}

	public function test_EmptyContext_Stringify_ShouldReturnEmptyString() {
		$context = [];

		$stringified_context = $this->context_stringifier->stringify($context);

		$this->assertSame('', $stringified_context);
	}

	public function test_ContextWithValue_Stringify_ShouldReturnKeyAndValue() {
		$context = [self::KEY => self::VALUE];

		$stringified_context = $this->context_stringifier->stringify($context);

		$this->assertEquals(self::KEY.': '.self::VALUE, $stringified_context);
	}

	public function test_ContextWithArrayValue_Stringify_ShouldReturnPrintedArray() {
		$array_value = [1,2,3];
		$context = [self::KEY => $array_value];

		$stringified_context = $this->context_stringifier->stringify($context);

		$this->assertEquals(self::KEY.': '.print_r($array_value, true), $stringified_context);
	}

	public function test_ContextWithObjectWithoutToString_Stringify_ShouldReturnPrintedObject() {
		$object = new ObjectWithoutToString();
		$object->property = self::A_STRING;
		$context = [self::KEY => $object];

		$stringified_context = $this->context_stringifier->stringify($context);

		$this->assertEquals(self::KEY.': '.print_r($object, true), $stringified_context);
	}

	public function test_ContextWithObjectWithToString_Stringify_ShouldReturnObjectAsString() {
		$object = new ObjectWithToString();
		$object->property = self::A_STRING;
		$context = [self::KEY => $object];

		$stringified_context = $this->context_stringifier->stringify($context);

		$this->assertEquals(self::KEY.': '.$object->__toString(), $stringified_context);
	}

	public function test_ContextWithMoreThanOneKey_Stringify_ShouldReturnBothValuesJoinedByEOL() {
		$context = [
			self::FIRST_KEY => self::FIRST_VALUE,
			self::SECOND_KEY => self::SECOND_VALUE
		];

		$stringified_context = $this->context_stringifier->stringify($context);

		$expected_string = self::FIRST_KEY.': '.self::FIRST_VALUE.PHP_EOL.self::SECOND_KEY.': '.self::SECOND_VALUE;
		$this->assertEquals($expected_string, $stringified_context);
	}

	public function test_ExcludeKey_Stringify_ShouldNotStringifyContextKey() {
		$context = [
			self::FIRST_KEY => self::FIRST_VALUE,
			self::SECOND_KEY => self::SECOND_VALUE
		];
		$this->context_stringifier->excludeKey(self::FIRST_KEY);

		$stringified_context = $this->context_stringifier->stringify($context);

		$expected_string = self::SECOND_KEY.': '.self::SECOND_VALUE;
		$this->assertEquals($expected_string, $stringified_context);
	}
}

class ObjectWithoutToString {
	public $property;
}

class ObjectWithToString {

	/**
	 * @var string
	 */
	public $property;

	public function __toString() {
		return $this->property;
	}
}
