<?php

namespace Kronos\Tests\Log\Traits;

use Kronos\Log\Traits\PrependDateTime;

class PrependDateTimeTest extends \PHPUnit_Framework_TestCase {

	const A_MESSAGE = 'a message';
	const DATETIME_REGEX = '\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]';

	private $datetime_prepender;

	public function setUp() {
		$this->datetime_prepender = $this->getMockForTrait(PrependDateTime::class);
	}

	public function test_NewPrepender_PrependDateTime_ShouldReturnGivenMessage() {
		$returned_message = $this->datetime_prepender->prependDateTime(self::A_MESSAGE);

		$this->assertSame(self::A_MESSAGE, $returned_message);
	}
	
	public function test_PrependerSetPrependDateTime_PrependDateTime_ShouldReturnGivenMessagePrependedWithTime() {
		$this->datetime_prepender->setPrependDateTime();
		
		$returned_message = $this->datetime_prepender->prependDateTime(self::A_MESSAGE);

		$this->assertRegExp('/'.self::DATETIME_REGEX.' '.self::A_MESSAGE.'/', $returned_message);
	}
}