<?php

namespace Kronos\Tests\Log;

use Kronos\Log\SettingsFormatter;

class SettingsFormatterTest extends \PHPUnit_Framework_TestCase {

	const SOME_CHANGES = ['logdna' => ['application' => 'app_test']];
	const SOME_OTHER_CHANGES = ['sentry' => ['projectId' => '123']];
	const SOME_CHANGE_TARGETING_AN_EXISTING_SETTING = ['sentry' => ['setting5' => 'abcde']];
	const EMPTY_SETTINGS = [];

	/**
	 * @var SettingsFormatter
	 */
	private $settings_formatter;

	public function setUp(){
		$this->settings_formatter = new SettingsFormatter($this->givenAConfigStruct());
	}

	public function test_givenALogConfigurationStructure_getWriters_shouldReturnAnArrayOfLogWriters(){
		$writers = $this->settings_formatter->getWriters();

		$this->assertEquals($this->thenAnArrayOfLogWriters(), $writers);
	}

	public function test_givenALogConfigurationStructureAndChangesToMakeToStruct_getFormattedSettings_shouldReturnTheConfigStructWithChanges(){
		$this->settings_formatter->setChanges(self::SOME_CHANGES);

		$formatted_settings = $this->settings_formatter->getFormattedSettings();

		$this->assertEquals($this->thenAnArrayOfLogWritersWithSomeChanges(), $formatted_settings);
	}

	public function test_givenALogConfigurationStructureAndNoChangesToStruct_getFormattedSettings_shouldReturnAnArrayOfLogWriters(){
		$formatted_settings = $this->settings_formatter->getFormattedSettings();

		$this->assertEquals($this->thenAnArrayOfLogWriters(), $formatted_settings);
	}

	public function test_givenAnEmptySettingsArrayAndNoChanges_getFormattedSettings_shouldReturnAnEmptyArray(){
		$settings_formatter = new SettingsFormatter();

		$formatted_settings = $settings_formatter->getFormattedSettings();

		$this->assertEquals(self::EMPTY_SETTINGS, $formatted_settings);
	}

	public function test_givenALogConfigurationStructureAndChangesToStructTargetingAndExistingSetting_getFormattedSettings_shouldReturnAnArrayOfLogWritersWithTheTargetedSettingsSwappedOut(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$settings_formatter->setChanges(self::SOME_CHANGE_TARGETING_AN_EXISTING_SETTING);

		$formatted_settings = $settings_formatter->getFormattedSettings();

		$this->assertEquals($this->thenAnArrayOfLogWritersWithOneOfTheWriterSettingSwappedOutForAnother(), $formatted_settings);
	}

	public function test_givenToolLogModesArray_getToolCurrentLogModesTuple_ShouldReturnATupleContainingAnArrayOfActiveToolLogModeNames(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct(), [], $this->givenToolLogModesArray());

		$tuple = $this->invokeMethod($settings_formatter, 'getToolCurrentLogModesTuple');

		$this->assertEquals($this->thenAnArrayOfActiveToolLogModes(), $tuple['active_modes']);
	}

	public function test_givenToolLogModesArray_getToolCurrentLogModesTuple_ShouldReturnATupleContainingAnArrayOfInactiveToolLogModeNames(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct(), [], $this->givenToolLogModesArray());

		$tuple = $this->invokeMethod($settings_formatter, 'getToolCurrentLogModesTuple');

		$this->assertEquals($this->thenAnArrayOfInactiveToolLogModes(), $tuple['inactive_modes']);
	}

	public function test_givenAnArrayOfLogWritersWithDeletionMarkers_deleteMarkedWritersToDelete_ShouldReturnAnArrayOfLogWritersWithOnlyAWriterHavingItsDeletionMarkerToFalse(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers_with_markers = $this->thenAnArrayOfLogWritersWithDeletionMarkers();

		$this->invokeMethod($settings_formatter, 'deleteMarkedWritersToDelete', [&$writers_with_markers]);

		$this->assertEquals($this->thenAnArrayOfLogWritersWithOnlyAWriterHavingItsDeletionMarkerToFalse(), $writers_with_markers);
	}

	public function test_thenAnArrayOfLogWritersWithAllDeletionMarkersSetToFalse_deleteMarkedWritersToDelete_ShouldReturnAnArrayOfLogWritersWithNoneOfThemHavingBeenDeleted(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers_with_markers = $this->thenAnArrayOfLogWritersWithAllDeletionMarkersSetToFalse();

		$this->invokeMethod($settings_formatter, 'deleteMarkedWritersToDelete', [&$writers_with_markers]);

		$this->assertEquals($this->thenAnArrayOfLogWritersWithAllDeletionMarkersSetToFalse(), $writers_with_markers);
	}

	public function test_thenAnArrayOfLogWriterAndToolSetToDebugModeAndAnArrayOfActiveToolLogModeIncludingDebug_markToDelete_ShouldReturnAnArrayOfLogWritersWithOnlyAWriterHavingItsDeletionMarkerToTrue(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$log_writers = $this->thenAnArrayOfLogWriter();

		$this->invokeMethod($settings_formatter, 'markToDelete', [&$log_writers, 0, ['debug'], $this->thenAnArrayOfActiveToolLogModes()]);

		$this->assertEquals($this->thenAnArrayOfLogWritersWithOnlyAWriterHavingItsDeletionMarkerToTrue(), $log_writers);
	}

	public function test_thenAnArrayOfLogWriterAndToolSetToDebugModeAndAnArrayOfActiveToolLogModeIncludingDebug_markToDelete_ShouldReturnAnArrayOfLogWritersWithOnlyAWriterHavingItsDeletionMarkerNoSet(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$log_writers = $this->thenAnArrayOfLogWriter();

		$this->invokeMethod($settings_formatter, 'markToDelete', [&$log_writers, 0, ['debug'], $this->thenAnArrayOfInactiveToolLogModes()]);

		$this->assertEquals($this->thenAnArrayOfLogWriter(), $log_writers);
	}

	/**
	 * Call protected/private method of a class.
	 *
	 * @param object &$object    Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array  $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	private function invokeMethod(&$object, $methodName, array $parameters = array())
	{
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}

	private function thenAnArrayOfActiveToolLogModes(){
		return ['debug'];
	}

	private function thenAnArrayOfInactiveToolLogModes(){
		return ['verbose', 'dry-run'];
	}

	private function givenToolLogModesArray(){
		return [
			'verbose' => false,
			'debug' => true,
			'dry-run' => false,

		];
	}

	private function thenAnArrayOfLogWritersWithDeletionMarkers(){
		return  [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true
				],
				'to_delete' => false
			],
			[
				'type' => 'logdna',
				'settings' => [
					'setting1' => false,
					'setting2' => true,
					'setting3' => 'application',
					'setting4' => 'dfdskfjhsdf90f09803melfkmds903j3' // random
				],
				'to_delete' => true
			],
			[
				'type' => 'sentry',
				'settings' => [
					'setting1' => 'error',
					'setting2' => true,
					'setting3' => 'sdfijsdlkfjsdlkfjsldkfjj3k544534098', // random
					'setting4' => 'dfsdlfkjsdf8734892394bn3kj3489073dd', // random
					'setting5' => '123456'
				],
				'to_delete' => true
			],
		];
	}

	private function thenAnArrayOfLogWritersWithAllDeletionMarkersSetToFalse(){
		return  [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true
				],
				'to_delete' => false
			],
			[
				'type' => 'logdna',
				'settings' => [
					'setting1' => false,
					'setting2' => true,
					'setting3' => 'application',
					'setting4' => 'dfdskfjhsdf90f09803melfkmds903j3' // random
				],
				'to_delete' => false
			],
			[
				'type' => 'sentry',
				'settings' => [
					'setting1' => 'error',
					'setting2' => true,
					'setting3' => 'sdfijsdlkfjsdlkfjsldkfjj3k544534098', // random
					'setting4' => 'dfsdlfkjsdf8734892394bn3kj3489073dd', // random
					'setting5' => '123456'
				],
				'to_delete' => false
			],
		];
	}

	private function thenAnArrayOfLogWritersWithOnlyAWriterHavingItsDeletionMarkerToFalse(){
		return  [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true
				],
				'to_delete' => false
			]
		];
	}

	private function thenAnArrayOfLogWritersWithOnlyAWriterHavingItsDeletionMarkerToTrue(){
		return  [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true
				],
				'to_delete' => true
			]
		];
	}

	private function givenAConfigStruct(){
		return ['writers' => [
						[
							'type' => 'file',
							'settings' => [
								'setting1' => '/path/to/some/file.log',
								'setting2' => true,
								'setting3' => true
							]
						],
						[
							'type' => 'logdna',
							'settings' => [
								'setting1' => false,
								'setting2' => true,
								'setting3' => 'application',
								'setting4' => 'dfdskfjhsdf90f09803melfkmds903j3' // random
							]
						],
						[
							'type' => 'sentry',
							'settings' => [
								'setting1' => 'error',
								'setting2' => true,
								'setting3' => 'sdfijsdlkfjsdlkfjsldkfjj3k544534098', // random
								'setting4' => 'dfsdlfkjsdf8734892394bn3kj3489073dd', // random
								'setting5' => '123456'
							]
						],
					]
			];
	}

	private function thenAnArrayOfLogWriters(){
		return  [
					[
						'type' => 'file',
						'settings' => [
							'setting1' => '/path/to/some/file.log',
							'setting2' => true,
							'setting3' => true
						]
					],
					[
						'type' => 'logdna',
						'settings' => [
							'setting1' => false,
							'setting2' => true,
							'setting3' => 'application',
							'setting4' => 'dfdskfjhsdf90f09803melfkmds903j3' // random
						]
					],
					[
						'type' => 'sentry',
						'settings' => [
							'setting1' => 'error',
							'setting2' => true,
							'setting3' => 'sdfijsdlkfjsdlkfjsldkfjj3k544534098', // random
							'setting4' => 'dfsdlfkjsdf8734892394bn3kj3489073dd', // random
							'setting5' => '123456'
						]
					],
			];
	}

	private function thenAnArrayOfLogWriter(){
		return  [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true
				]
			]
		];
	}

	private function thenAnArrayOfLogWritersWithSomeChanges(){
		return  [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true
				]
			],
			[
				'type' => 'logdna',
				'settings' => [
					'setting1' => false,
					'setting2' => true,
					'setting3' => 'application',
					'setting4' => 'dfdskfjhsdf90f09803melfkmds903j3', // random
					'application' => 'app_test' // random
				]
			],
			[
				'type' => 'sentry',
				'settings' => [
					'setting1' => 'error',
					'setting2' => true,
					'setting3' => 'sdfijsdlkfjsdlkfjsldkfjj3k544534098', // random
					'setting4' => 'dfsdlfkjsdf8734892394bn3kj3489073dd', // random
					'setting5' => '123456'
				]
			],
		];
	}

	private function thenAnArrayOfLogWritersWithOneOfTheWriterSettingSwappedOutForAnother(){
		return  [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true
				]
			],
			[
				'type' => 'logdna',
				'settings' => [
					'setting1' => false,
					'setting2' => true,
					'setting3' => 'application',
					'setting4' => 'dfdskfjhsdf90f09803melfkmds903j3', // random
				]
			],
			[
				'type' => 'sentry',
				'settings' => [
					'setting1' => 'error',
					'setting2' => true,
					'setting3' => 'sdfijsdlkfjsdlkfjsldkfjj3k544534098', // random
					'setting4' => 'dfsdlfkjsdf8734892394bn3kj3489073dd', // random
					'setting5' => 'abcde'
				]
			],
		];
	}
}