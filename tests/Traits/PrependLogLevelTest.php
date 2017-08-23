<?php

namespace Kronos\Tests\Log\Traits;

use Kronos\Log\Traits\PrependLogLevel;
use Psr\Log\LogLevel;

class PrependLogLevelTest extends \PHPUnit_Framework_TestCase {

	const A_MESSAGE = 'a message';
	const ANY_LOG_LEVEL = LogLevel::INFO;

	private $loglevel_prepender;

	public function setUp() {
		$this->loglevel_prepender = $this->getMockForTrait(PrependLogLevel::class);
	}

	public function test_NewPrepender_PrependLogLevel_ShouldReturnGivenMessage() {
		$returned_message = $this->loglevel_prepender->prependLogLevel(self::ANY_LOG_LEVEL, self::A_MESSAGE);

		$this->assertEquals(self::A_MESSAGE, $returned_message);
	}

	public function test_PrependerSetPrependLogLevel_PrependLogLevel_ShouldPrependMessageWithLogLevelInUpperCase() {
		$this->loglevel_prepender->setPrependLogLevel();

		$returned_message = $this->loglevel_prepender->prependLogLevel(self::ANY_LOG_LEVEL, self::A_MESSAGE);

		$this->assertEquals(strtoupper(self::ANY_LOG_LEVEL).' : '.self::A_MESSAGE, $returned_message);
	}
}