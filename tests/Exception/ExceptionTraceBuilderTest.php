<?php

namespace Kronos\Tests\Log;

use Kronos\Log\Exception\ExceptionTraceBuilder;

class ExceptionTraceBuilderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $line_builder;

	/**
	 * @var ExceptionTraceBuilder
	 */
	private $exception_trace_builder;

	public function setUp(){
		$this->line_builder = new \Kronos\Log\Exception\LineBuilder();
		$this->exception_trace_builder = new \Kronos\Log\Exception\ExceptionTraceBuilder($this->line_builder);
	}

	public function test_givenAnExceptionTraceArrayWithNoIncludeArgumentsOption_getTraceAsString_shouldReturnFormattedExceptionStackTraceWithoutArguments(){
		$trace_as_string = $this->exception_trace_builder->getTraceAsString(new TestableException(), false);

		$this->assertEquals($this->thenAFormattedStackAsTraceWithoutArguments(), $trace_as_string);
	}

	public function test_givenAnExceptionTraceArrayWithIncludeArgumentsOption_getTraceAsString_shouldReturnFormattedExceptionStackTraceWithArguments(){
		$trace_as_string = $this->exception_trace_builder->getTraceAsString(new TestableException(), true);

		$this->assertEquals($this->thenAFormattedStackAsTraceWithArguments(), $trace_as_string);
	}

	public function thenAFormattedStackAsTraceWithoutArguments(){
		return '#0 /srv/kronos/crm/modules/Core/Tool/Testerino.php(20): Core__Tool_Testerino->a()
#1 /srv/kronos/crm/vendor/kronostechnologies/kronos-lib/Kronos/Common/Tool.php(478): Core__Tool_Testerino->runTool()
#2 /srv/kronos/crm/vendor/kronostechnologies/kronos-lib/Kronos/Common/CLI.php(197): Kronos\Common\Tool->run()
#3 /srv/kronos/crm/vendor/kronostechnologies/kronos-lib/Kronos/Common/CLI.php(59): Kronos\Common\CLI->runTool()
#4 /srv/kronos/crm/script/tool.php(35): Kronos\Common\CLI->run()
#5 /srv/kronos/crm/script/tool(4): include()
';
	}

	public function thenAFormattedStackAsTraceWithArguments(){
		return '#0 /srv/kronos/crm/modules/Core/Tool/Testerino.php(20): Core__Tool_Testerino->a(1,2,Array)
#1 /srv/kronos/crm/vendor/kronostechnologies/kronos-lib/Kronos/Common/Tool.php(478): Core__Tool_Testerino->runTool()
#2 /srv/kronos/crm/vendor/kronostechnologies/kronos-lib/Kronos/Common/CLI.php(197): Kronos\Common\Tool->run()
#3 /srv/kronos/crm/vendor/kronostechnologies/kronos-lib/Kronos/Common/CLI.php(59): Kronos\Common\CLI->runTool()
#4 /srv/kronos/crm/script/tool.php(35): Kronos\Common\CLI->run()
#5 /srv/kronos/crm/script/tool(4): include(/srv/kronos/crm/script/tool.php)
';
	}
}

class TestableException {

	public function getTrace(){
		return [
			0 => [
				'file' => '/srv/kronos/crm/modules/Core/Tool/Testerino.php',
				'line' => 20,
				'function' => 'a',
				'class' => 'Core__Tool_Testerino',
				'type' => '->',
				'args' => [
					0 => 1,
					1 => 2,
					2 => [
						'test' => 'test_value'
					],
				],
			],
			1 => [
				'file' => '/srv/kronos/crm/vendor/kronostechnologies/kronos-lib/Kronos/Common/Tool.php',
				'line' => 478,
				'function' => 'runTool',
				'class' => 'Core__Tool_Testerino',
				'type' => '->',
				'args' => [],
			],
			2 => [
				'file' => '/srv/kronos/crm/vendor/kronostechnologies/kronos-lib/Kronos/Common/CLI.php',
				'line' => 197,
				'function' => 'run',
				'class' => 'Kronos\Common\Tool',
				'type' => '->',
				'args' => [],
			],
			3 => [
				'file' => '/srv/kronos/crm/vendor/kronostechnologies/kronos-lib/Kronos/Common/CLI.php',
				'line' => 59,
				'function' => 'runTool',
				'class' => 'Kronos\Common\CLI',
				'type' => '->',
				'args' => [],
			],
			4 => [
				'file' => '/srv/kronos/crm/script/tool.php',
				'line' => 35,
				'function' => 'run',
				'class' => 'Kronos\Common\CLI',
				'type' => '->',
				'args' => [],
			],
			5 => [
				'file' => '/srv/kronos/crm/script/tool',
				'line' => 4,
				'function' => 'include',
				'args' => [
					0 => '/srv/kronos/crm/script/tool.php'
				],
			]
		];
	}
}