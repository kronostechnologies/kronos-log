<?php

namespace Kronos\Tests\Log;

use Kronos\Log\Traits\Interpolate;

class InterpolateTest extends \PHPUnit_Framework_TestCase {

	const A_MESSAGE = 'Some message {key}';
	const INTERPOLATED_MESSAGE = 'Some message value';
	const KEY = 'key';
	const VALUE = 'value';
	const MESSAGE_WITH_UNDEFINED = 'Some message ~UNDEFINED~';

	private $interpolator;

	public function setUp() {
		$this->interpolator = $this->getMockForTrait(Interpolate::class);
	}

	public function test_MessageWithPlaceholder_Interpolate_ShouldReplacePlaceholderWithContextValue() {
		$original_message = self::A_MESSAGE;

		$interpolated_message = $this->interpolator->interpolate($original_message, [self::KEY => self::VALUE]);

		$this->assertEquals(self::INTERPOLATED_MESSAGE, $interpolated_message);
	}

	public function test_ContextWithoutMessagePlaceholder_Interpolate_ShouldReplacePlaceholderWithUndefined() {
		$original_message = self::A_MESSAGE;

		$interpolated_message = $this->interpolator->interpolate($original_message, []);

		$this->assertEquals(self::MESSAGE_WITH_UNDEFINED, $interpolated_message);
	}
}