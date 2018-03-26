<?php

namespace Kronos\Tests\Log;

use Kronos\Log\SettingsFormatter;

class SettingsFormatterTest extends \PHPUnit_Framework_TestCase {

	public function test_ALogConfigurationStructure_getWriters_shouldReturnAnArrayOfLogWriters(){
        $settingsFormatter = new SettingsFormatter();
        $settingsFormatter->setSettings($this->givenAConfigStruct());

		$writers = $settingsFormatter->getWriters();

		$this->assertEquals($this->anArrayOfLogWriters(), $writers);
	}

	public function test_ALogConfigurationStructureAndChangesToMakeToStruct_getFormattedSettings_shouldReturnTheConfigStructWithChanges(){
        $settingsFormatter = new SettingsFormatter();
        $settingsFormatter->setSettings($this->givenAConfigStruct());
		$settingsFormatter->setWriterSpecificChanges(self::SOME_CHANGES);

		$formattedSettings = $settingsFormatter->getFormattedSettings();

		$this->assertEquals($this->anArrayOfLogWritersWithSomeChanges(), $formattedSettings);
	}

	public function test_ALogConfigurationStructureAndNoChangesToStruct_getFormattedSettings_shouldReturnAnArrayOfLogWriters(){
        $settingsFormatter = new SettingsFormatter();
        $settingsFormatter->setSettings($this->givenAConfigStruct());

		$formattedSettings = $settingsFormatter->getFormattedSettings();

		$this->assertEquals($this->anArrayOfLogWriters(), $formattedSettings);
	}

	public function test_AnEmptySettingsArrayAndNoChanges_getFormattedSettings_shouldReturnAnEmptyArray(){
		$settingsFormatter = new SettingsFormatter();

		$formattedSettings = $settingsFormatter->getFormattedSettings();

		$this->assertEquals(self::EMPTY_SETTINGS, $formattedSettings);
	}

	public function test_ALogConfigurationStructureAndChangesToStructTargetingAndExistingSetting_getFormattedSettings_shouldReturnAnArrayOfLogWritersWithTheTargetedSettingsSwappedOut(){
		$settingsFormatter = new SettingsFormatter();
        $settingsFormatter->setSettings($this->givenAConfigStruct());
        $settingsFormatter->setWriterSpecificChanges(self::SOME_CHANGE_TARGETING_AN_EXISTING_SETTING);

		$formattedSettings = $settingsFormatter->getFormattedSettings();

		$this->assertEquals($this->anArrayOfLogWritersWithOneOfTheWriterSettingSwappedOutForAnother(), $formattedSettings);
	}

	public function test_LogFlagsArray_getActiveFlags_ShouldReturnAnArrayOfActiveLogFlagNames(){
		$settingsFormatter = new SettingsFormatter();
        $settingsFormatter->setSettings($this->givenAConfigStruct());
        $settingsFormatter->setFlags($this->givenLogFlagsArray());

		$active_modes = $this->invokeMethod($settingsFormatter, 'getActiveFlags');

		$this->assertEquals($this->anArrayOfActiveLogFlag(), $active_modes);
	}

	public function test_LogFlagsArrayAllSetToFalse_getActiveFlags_ShouldReturnAnEmptyArray(){
		$settingsFormatter = new SettingsFormatter($this->givenAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse(), [], $this->givenLogFlagsFalseArray());

		$active_modes = $this->invokeMethod($settingsFormatter, 'getActiveFlags');

		$this->assertEquals(self::NO_LOG_ACTIVE_MODES, $active_modes);
	}

	public function test_LogFlagSetToDebugAndAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse_setIncludeDebugLevelForWriters_shouldSetTheIncludeDebugLevelSettingToTrue(){
		$settingsFormatter = new SettingsFormatter($this->givenAConfigStruct(), [], $this->givenLogFlagsArray());

		$writers = $this->invokeMethod($settingsFormatter, 'setIncludeDebugLevelForWriters', [$this->givenAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse()]);

		$include_debug = $writers[0]['settings']['includeDebugLevel'];

		$this->assertTrue($include_debug);
	}

	public function test_LogFlagsNotSetToDebugAndAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse_setIncludeDebugLevelForWriters_shouldNotSetTheIncludeDebugLevelSettingToTrue(){
		$settingsFormatter = new SettingsFormatter($this->givenAConfigStruct(), [], $this->givenLogFlagsFalseArray());

		$writers = $this->invokeMethod($settingsFormatter, 'setIncludeDebugLevelForWriters', [$this->givenAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse()]);

		$include_debug = $writers[0]['settings']['includeDebugLevel'];

		$this->assertFalse($include_debug);
	}

	public function test_AnArrayOfLogWritersWithDeletionMarkers_deleteMarkedWritersToDelete_ShouldReturnAnArrayOfLogWritersWithOnlyAWriterHavingItsDeletionMarkerToFalse(){
		$settingsFormatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settingsFormatter, 'deleteMarkedWritersToDelete', [$this->anArrayOfLogWritersWithDeletionMarkers()]);

		$this->assertEquals($this->anArrayOfLogWritersWithOnlyAWriterHavingItsDeletionMarkerToFalse(), $writers);
	}

	public function test_AnArrayOfLogWritersWithAllDeletionMarkersSetToFalse_deleteMarkedWritersToDelete_ShouldReturnAnArrayOfLogWritersWithNoneOfThemHavingBeenDeleted(){
		$settingsFormatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settingsFormatter, 'deleteMarkedWritersToDelete', [$this->anArrayOfLogWritersWithAllDeletionMarkersSetToFalse()]);

		$this->assertEquals($this->anArrayOfLogWritersWithAllDeletionMarkersSetToFalse(), $writers);
	}

	public function test_markUnallowedWritersToDelete8(){
		$settingsFormatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settingsFormatter, 'markUnallowedWritersToDelete', [self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::DRY_RUN]]);

		$this->assertEquals(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, $writers);
	}

	public function test_ABasicLogWritersConfigSettingsArrayWithNoActivateOrDeactivateWithFlagOptions_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayNotMarkedToDelete(){
		$settingsFormatter = new SettingsFormatter($this->givenAConfigStruct());

		$settings_array = $this->addDeactivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::VERBOSE, self::DEBUG]);
		$settings_array_to_delete_false = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, false);

		$writers = $this->invokeMethod($settingsFormatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN]]);

		$this->assertEquals($settings_array_to_delete_false, $writers);
	}

	public function test_ALogWriterConfigSettingsArrayWithTwoDeactivateWithFlagsAndLogFlagsHavingOneAllowedAndOneUnallowed_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayMarkedToDelete(){
        $settingsFormatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addDeactivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::VERBOSE, self::DEBUG]);
        $settings_array_to_delete_true = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, true);

        $writers = $this->invokeMethod($settingsFormatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN, self::VERBOSE]]);

        $this->assertEquals($settings_array_to_delete_true, $writers);
	}

	public function test_ALogWriterConfigSettingsArrayWithThreeDeactivateWithFlagsAndLogFlagsHavingTwoUnallowed_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayMarkedToDelete(){
        $settingsFormatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addDeactivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::VERBOSE, self::DEBUG, self::DRY_RUN]);
        $settings_array_to_delete_true = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, true);

        $writers = $this->invokeMethod($settingsFormatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN, self::VERBOSE]]);

        $this->assertEquals($settings_array_to_delete_true, $writers);
	}

	public function test_ALogWriterConfigSettingsArrayWithTwoActivateWithFlagsAndLogFlagsHavingOneAllowed_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayNotMarkedToDelete(){
        $settingsFormatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::VERBOSE, self::DEBUG]);
        $settings_array_to_delete_false = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, false);

        $writers = $this->invokeMethod($settingsFormatter, 'markUnallowedWritersToDelete', [$settings_array, [self::VERBOSE]]);

        $this->assertEquals($settings_array_to_delete_false, $writers);
	}

	public function test_ALogWriterConfigSettingsArrayWithTwoActivateWithFlagsAndLogFlagsHavingOneAllowedAndOneUnallowed_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayNotMarkedToDelete(){
        $settingsFormatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::VERBOSE, self::DEBUG]);
        $settings_array_to_delete_false = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, false);

        $writers = $this->invokeMethod($settingsFormatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN, self::VERBOSE]]);

        $this->assertEquals($settings_array_to_delete_false, $writers);
	}

	public function test_ALogWriterConfigSettingsArrayWithTwoActivateWithFlagsAndLogFlagsHavingOneUnallowed_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayMarkedToDelete(){
        $settingsFormatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::VERBOSE, self::DEBUG]);
        $settings_array_to_delete_true = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, true);

        $writers = $this->invokeMethod($settingsFormatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN]]);

        $this->assertEquals($settings_array_to_delete_true, $writers);
	}

	// else if(!empty($config_activate_with_flags) && !empty($config_deactivate_with_flags))
	public function test_ALogWriterConfigSettingsArrayWithBothActivateAndDeactivateWithFlagsAndAndAllowedLogFlag_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayNotMarkedToDelete(){
        $settingsFormatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::DRY_RUN]);
        $settings_array = $this->addDeactivateWithFlagOptions($settings_array, [self::DEBUG]);
        $settings_array_to_delete_false = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, false);

        $writers = $this->invokeMethod($settingsFormatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN]]);

        $this->assertEquals($settings_array_to_delete_false, $writers);
	}

	public function test_ALogWriterConfigSettingsArrayWithBothActivateAndDeactivateWithFlagsAndAndUnallowedLogFlag_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayMarkedToDelete(){
        $settingsFormatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::DRY_RUN]);
        $settings_array = $this->addDeactivateWithFlagOptions($settings_array, [self::DEBUG]);
        $settings_array_to_delete_true = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, true);

        $writers = $this->invokeMethod($settingsFormatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DEBUG]]);

        $this->assertEquals($settings_array_to_delete_true, $writers);
	}

	public function test_ALogWriterConfigSettingsArrayWithOneActivateWithFlagAndTwoDeactivateWithFlagndUnallowedLogFlag_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayMarkedToDelete(){
        $settingsFormatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::DRY_RUN]);
        $settings_array = $this->addDeactivateWithFlagOptions($settings_array, [self::VERBOSE, self::DEBUG]);
        $settings_array_to_delete_true = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, true);

        $writers = $this->invokeMethod($settingsFormatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DEBUG]]);

        $this->assertEquals($settings_array_to_delete_true, $writers);
	}

	public function test_ALogWriterConfigSettingsArrayWithOneActivateWithFlagAndTwoDeactivateWithFlagAndOneAllowedLogFlagAndOneUnallowedLogFlag_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayMarkedToDelete(){
        $settingsFormatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::DRY_RUN]);
        $settings_array = $this->addDeactivateWithFlagOptions($settings_array, [self::VERBOSE, self::DEBUG]);
        $settings_array_to_delete_true = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, true);

        $writers = $this->invokeMethod($settingsFormatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN, self::DEBUG]]);

        $this->assertEquals($settings_array_to_delete_true, $writers);
	}

	public function test_ALogWriterConfigSettingsArrayWithOneActivateWithFlagAndThreeDeactivateWithFlagWithOneBeingTheSameAsAllowed_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayMarkedToDelete(){
        $settingsFormatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::DRY_RUN]);
        $settings_array = $this->addDeactivateWithFlagOptions($settings_array, [self::VERBOSE, self::DEBUG, self::DRY_RUN]);
        $settings_array_to_delete_true = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, true);

        $writers = $this->invokeMethod($settingsFormatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN]]);

        $this->assertEquals($settings_array_to_delete_true, $writers);
	}

    const SOME_CHANGES = ['logdna' => ['application' => 'app_test']];
    const SOME_OTHER_CHANGES = ['sentry' => ['projectId' => '123']];
    const SOME_CHANGE_TARGETING_AN_EXISTING_SETTING = ['sentry' => ['setting5' => 'abcde']];
    const DEBUG = 'debug';
    const DRY_RUN = 'dry-run';
    const VERBOSE = 'verbose';
    const EMPTY_SETTINGS = [];
    const NO_LOG_ACTIVE_MODES = [];
    const BASIC_LOG_WRITERS_CONFIG_SETTINGS = [
        [
            'type' => 'file',
            'settings' => [
                'setting1' => '/path/to/some/file.log',
                'setting2' => true,
                'setting3' => true
            ]
        ]
    ];

	private function anArrayOfActiveLogFlag(){
		return ['debug'];
	}

	private function givenLogFlagsArray(){
		return [
			'verbose' => false,
			'debug' => true,
			'dry-run' => false,

		];
	}

	private function givenLogFlagsFalseArray(){
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

	private function anArrayOfLogWritersWithDeletionMarkers(){
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

	private function anArrayOfLogWritersWithAllDeletionMarkersSetToFalse(){
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

	private function anArrayOfLogWritersWithOnlyAWriterHavingItsDeletionMarkerToFalse(){
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

	private function anArrayOfLogWriters(){
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

	private function anArrayOfLogWritersWithSomeChanges(){
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

	private function anArrayOfLogWritersWithOneOfTheWriterSettingSwappedOutForAnother(){
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

    // UTIL FUNCTIONS

    private function addActivateWithFlagOptions($settings_array = [], $activate_with_flag_values = []){
        $arr = $settings_array;

        $arr[0]['settings']['activateWithFlag'] = $activate_with_flag_values;

        return $arr;
    }

    private function addDeactivateWithFlagOptions($settings_array = [], $deactivate_with_flag_values = []){
        $arr = $settings_array;

        $arr[0]['settings']['deactivateWithFlag'] = $deactivate_with_flag_values;

        return $arr;
    }

    private function addToDeleteMarkerToLogConfigSettingsArray($settings_array = [], $to_delete = true){
        $arr = $settings_array;

        $arr[0]['to_delete'] = $to_delete;

        return $arr;
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