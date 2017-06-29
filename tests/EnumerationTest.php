<?php

namespace Kronos\Tests\Log;

use Kronos\Log\Enumeration;

class EnumerationTest extends \PHPUnit_Framework_TestCase {

	const VALID_NAME = 'FIRST_VALUE';
	const INVALID_NAME = 'NOT_A_CONST';
	const INVALID_ENUM_VALUE = 'invalid value';

	public function test_ValidName_IsValidName_ShouldReturnTrue() {
		$name = self::VALID_NAME;

		$is_valid_name = TestableEnumeration::isValidName($name);

		$this->assertTrue($is_valid_name);
	}

	public function test_InvalidName_IsValidName_ShouldReturnFalse() {
		$name = self::INVALID_NAME;

		$is_valid_name = TestableEnumeration::isValidName($name);

		$this->assertFalse($is_valid_name);
	}

	public function test_ValidValue_IsValidValue_ShouldReturnTrue() {
		$value = TestableEnumeration::FIRST_VALUE;

		$is_valid_value = TestableEnumeration::isValidValue($value);

		$this->assertTrue($is_valid_value);
	}

	public function test_InvalidValue_IsValidValue_ShouldReturnFalse() {
		$value = self::INVALID_ENUM_VALUE;

		$is_valid_value = TestableEnumeration::isValidValue($value);

		$this->assertFalse($is_valid_value);
	}
}

class TestableEnumeration extends Enumeration {
	const FIRST_VALUE = 'first value';
	const SECOND_VALUE = 'second value';
}