<?php

namespace Kronos\Tests\Log;

use Kronos\Log\SettingsFormatter;

class SettingsFormatterTest extends \PHPUnit_Framework_TestCase {

	const SOME_CHANGES = ['logdna' => ['application' => 'app_test']];
	const SOME_OTHER_CHANGES = ['sentry' => ['projectId' => '123']];
	const SOME_CHANGE_TARGETING_AN_EXISTING_SETTING = ['sentry' => ['setting5' => 'abcde']];
	const EMPTY_SETTINGS = [];
	const NO_TOOL_LOG_ACTIVE_MODES = [];

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

	public function test_givenToolLogModesArray_getActiveToolLogModes_ShouldReturnAnArrayOfActiveToolLogModeNames(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct(), [], $this->givenToolLogModesArray());

		$active_modes = $this->invokeMethod($settings_formatter, 'getActiveToolLogModes');

		$this->assertEquals($this->thenAnArrayOfActiveToolLogMode(), $active_modes);
	}

	public function test_givenToolLogModesArrayAllSetToFalse_getActiveToolLogModes_ShouldReturnAnEmptyArray(){
		$settings_formatter = new SettingsFormatter($this->givenAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse(), [], $this->givenToolLogModesFalseArray());

		$active_modes = $this->invokeMethod($settings_formatter, 'getActiveToolLogModes');

		$this->assertEquals(self::NO_TOOL_LOG_ACTIVE_MODES, $active_modes);
	}

	public function test_givenToolLogModeSetToDebugAndAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse_setIncludeDebugLevelForWriters_shouldSetTheIncludeDebugLevelSettingToTrue(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct(), [], $this->givenToolLogModesArray());

		$writers = $this->invokeMethod($settings_formatter, 'setIncludeDebugLevelForWriters', [$this->givenAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse()]);

		$include_debug = $writers[0]['settings']['includeDebugLevel'];

		$this->assertTrue($include_debug);
	}

	public function test_givenToolLogModesNotSetToDebugAndAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse_setIncludeDebugLevelForWriters_shouldNotSetTheIncludeDebugLevelSettingToTrue(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct(), [], $this->givenToolLogModesFalseArray());

		$writers = $this->invokeMethod($settings_formatter, 'setIncludeDebugLevelForWriters', [$this->givenAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse()]);

		$include_debug = $writers[0]['settings']['includeDebugLevel'];

		$this->assertFalse($include_debug);
	}

	public function test_givenAnArrayOfLogWritersWithDeletionMarkers_deleteMarkedWritersToDelete_ShouldReturnAnArrayOfLogWritersWithOnlyAWriterHavingItsDeletionMarkerToFalse(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'deleteMarkedWritersToDelete', [$this->thenAnArrayOfLogWritersWithDeletionMarkers()]);

		$this->assertEquals($this->thenAnArrayOfLogWritersWithOnlyAWriterHavingItsDeletionMarkerToFalse(), $writers);
	}

	public function test_givenAnArrayOfLogWritersWithAllDeletionMarkersSetToFalse_deleteMarkedWritersToDelete_ShouldReturnAnArrayOfLogWritersWithNoneOfThemHavingBeenDeleted(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'deleteMarkedWritersToDelete', [$this->thenAnArrayOfLogWritersWithAllDeletionMarkersSetToFalse()]);

		$this->assertEquals($this->thenAnArrayOfLogWritersWithAllDeletionMarkersSetToFalse(), $writers);
	}

	// UTIL FUNCTIONS
	private function aBasicLogConfigSettingsArrayWithNoActivateWithOrDeactivateWithOptions(){
		return [
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

	private function addOptionToLogConfigSettingsArray($option, $value = []){
		return $this->aBasicLogConfigSettingsArrayWithNoActivateWithOrDeactivateWithOptions()['settings'][$option] = $value;
	}

	private function addActivateWithFlagOptions($options = []){
		if (!is_array($options)){
			$options = [$options];
		}

		return $this->addOptionToLogConfigSettingsArray('activateWithFlag', $options);
	}

	private function addDeactivateWithFlagOptions($options = []){
		if (!is_array($options)){
			$options = [$options];
		}

		return $this->addOptionToLogConfigSettingsArray('deactivateWithFlag', $options);
	}

	private function addToDeleteMarkerToLogConfigSettingsArray($settings_array = [], $to_delete = true){
		return $settings_array['to_delete'] = $to_delete;
	}

	// if (empty($config_activate_with_flags) && empty($config_deactivate_with_flags))
	public function test_markUnallowedWritersToDelete8(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$this->aBasicLogConfigSettingsArrayWithNoActivateWithOrDeactivateWithOptions(), ['dry-run']]);

		$this->assertEquals($this->aBasicLogConfigSettingsArrayWithNoActivateWithOrDeactivateWithOptions(), $writers);
	}

	// else if(empty($config_activate_with_flags) && !empty($config_deactivate_with_flags))
	public function test_markUnallowedWritersToDelete4(){
		$array = $this->addDeactivateWithFlagOptions()

		$a = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'deactivateWithFlag' => ['verbose', 'debug']
				]
			]
		];

		$b = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'deactivateWithFlag' => ['verbose', 'debug']
				],
				'to_delete' => false
			]
		];


		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$a, ['dry-run']]);

		$this->assertEquals($b, $writers);
	}

	public function test_markUnallowedWritersToDelete5(){
		$a = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'deactivateWithFlag' => ['verbose', 'debug']
				]
			]
		];

		$b = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'deactivateWithFlag' => ['verbose', 'debug']
				],
				'to_delete' => true
			]
		];


		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$a, ['dry-run', 'verbose']]);

		$this->assertEquals($b, $writers);
	}

	public function test_markUnallowedWritersToDelete6(){
		$a = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'deactivateWithFlag' => ['verbose', 'debug', 'dry-run']
				]
			]
		];

		$b = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'deactivateWithFlag' => ['verbose', 'debug', 'dry-run']
				],
				'to_delete' => true
			]
		];


		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$a, ['dry-run', 'verbose']]);

		$this->assertEquals($b, $writers);
	}

	// if (!empty($config_activate_with_flags) && empty($config_deactivate_with_flags))
	public function test_markUnallowedWritersToDelete7aaa(){
		$a = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['verbose', 'debug']
				]
			]
		];

		$b = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['verbose', 'debug']
				],
				'to_delete' => false
			]
		];


		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$a, ['verbose']]);

		$this->assertEquals($b, $writers);
	}

	public function test_markUnallowedWritersToDelete7(){
		$a = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['verbose', 'debug']
				]
			]
		];

		$b = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['verbose', 'debug']
				],
				'to_delete' => false
			]
		];


		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$a, ['dry-run', 'verbose']]);

		$this->assertEquals($b, $writers);
	}

	public function test_markUnallowedWritersToDelete7b(){
		$a = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['verbose', 'debug']
				]
			]
		];

		$b = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['verbose', 'debug']
				],
				'to_delete' => true
			]
		];


		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$a, ['dry-run']]);

		$this->assertEquals($b, $writers);
	}

	// else if(!empty($config_activate_with_flags) && !empty($config_deactivate_with_flags))
	public function test_markUnallowedWritersToDelete1(){
		$a = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['dry-run'],
					'deactivateWithFlag' => ['debug']
				]
			]
		];

		$b = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['dry-run'],
					'deactivateWithFlag' => ['debug']
				],
				'to_delete' => false
			]
		];


		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$a, ['dry-run']]);

		$this->assertEquals($b, $writers);
	}

	public function test_markUnallowedWritersToDelete2(){
		$a = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['dry-run'],
					'deactivateWithFlag' => ['debug']
				]
			]
		];

		$b = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['dry-run'],
					'deactivateWithFlag' => ['debug']
				],
				'to_delete' => true
			]
		];


		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$a, ['debug']]);

		$this->assertEquals($b, $writers);
	}

	public function test_markUnallowedWritersToDelete3(){
		$a = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['dry-run'],
					'deactivateWithFlag' => ['verbose', 'debug']
				]
			]
		];

		$b = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['dry-run'],
					'deactivateWithFlag' => ['verbose', 'debug']
				],
				'to_delete' => true
			]
		];


		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$a, ['debug']]);

		$this->assertEquals($b, $writers);
	}

	public function test_markUnallowedWritersToDelete3v(){
		$a = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['dry-run'],
					'deactivateWithFlag' => ['verbose', 'debug']
				]
			]
		];

		$b = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['dry-run'],
					'deactivateWithFlag' => ['verbose', 'debug']
				],
				'to_delete' => true
			]
		];


		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$a, ['dry-run', 'debug']]);

		$this->assertEquals($b, $writers);
	}

	public function test_markUnallowedWritersToDelete3vv(){
		$a = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['dry-run'],
					'deactivateWithFlag' => ['verbose', 'debug', 'dry-run']
				]
			]
		];

		$b = [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'setting3' => true,
					'activateWithFlag' => ['dry-run'],
					'deactivateWithFlag' => ['verbose', 'debug', 'dry-run']
				],
				'to_delete' => true
			]
		];


		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$a, ['dry-run']]);

		$this->assertEquals($b, $writers);
	}

	private function thenAnArrayOfActiveToolLogMode(){
		return ['debug'];
	}

	private function givenToolLogModesArray(){
		return [
			'verbose' => false,
			'debug' => true,
			'dry-run' => false,

		];
	}

	private function givenToolLogModesFalseArray(){
		return [
			'verbose' => false,
			'debug' => false,
			'dry-run' => false,

		];
	}

	private function givenAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse(){
		return  [
			[
				'type' => 'file',
				'settings' => [
					'setting1' => '/path/to/some/file.log',
					'setting2' => true,
					'includeDebugLevel' => false
				]
			]
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

	/**
	 * Call protected/private method of a class.
	 *
	 * @param object &$object    Instantiated object that we will run method on.
	 * @param string $methodName Method name to call
	 * @param array  $parameters Array of parameters to pass into method.
	 *
	 * @return mixed Method return.
	 */
	private function invokeMethod(&$object, $methodName, array $parameters = []){
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $parameters);
	}
}