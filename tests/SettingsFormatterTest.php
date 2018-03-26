<?php

namespace Kronos\Tests\Log;

use Kronos\Log\SettingsFormatter;

class SettingsFormatterTest extends \PHPUnit_Framework_TestCase {

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

	public function test_givenLogFlagsArray_getActiveFlags_ShouldReturnAnArrayOfActiveLogFlagNames(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct(), [], $this->givenLogFlagsArray());

		$active_modes = $this->invokeMethod($settings_formatter, 'getActiveFlags');

		$this->assertEquals($this->thenAnArrayOfActiveLogFlag(), $active_modes);
	}

	public function test_givenLogFlagsArrayAllSetToFalse_getActiveFlags_ShouldReturnAnEmptyArray(){
		$settings_formatter = new SettingsFormatter($this->givenAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse(), [], $this->givenLogFlagsFalseArray());

		$active_modes = $this->invokeMethod($settings_formatter, 'getActiveFlags');

		$this->assertEquals(self::NO_LOG_ACTIVE_MODES, $active_modes);
	}

	public function test_givenLogFlagSetToDebugAndAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse_setIncludeDebugLevelForWriters_shouldSetTheIncludeDebugLevelSettingToTrue(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct(), [], $this->givenLogFlagsArray());

		$writers = $this->invokeMethod($settings_formatter, 'setIncludeDebugLevelForWriters', [$this->givenAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse()]);

		$include_debug = $writers[0]['settings']['includeDebugLevel'];

		$this->assertTrue($include_debug);
	}

	public function test_givenLogFlagsNotSetToDebugAndAnArrayOfLogWriterWithIncludeDebugLevelSetToFalse_setIncludeDebugLevelForWriters_shouldNotSetTheIncludeDebugLevelSettingToTrue(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct(), [], $this->givenLogFlagsFalseArray());

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

	public function test_markUnallowedWritersToDelete8(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::DRY_RUN]]);

		$this->assertEquals(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, $writers);
	}

	public function test_givenABasicLogWritersConfigSettingsArrayWithNoActivateOrDeactivateWithFlagOptions_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayNotMarkedToDelete(){
		$settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

		$settings_array = $this->addDeactivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::VERBOSE, self::DEBUG]);
		$settings_array_to_delete_false = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, false);

		$writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN]]);

		$this->assertEquals($settings_array_to_delete_false, $writers);
	}

	public function test_givenALogWriterConfigSettingsArrayWithTwoDeactivateWithFlagsAndLogFlagsHavingOneAllowedAndOneUnallowed_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayMarkedToDelete(){
        $settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addDeactivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::VERBOSE, self::DEBUG]);
        $settings_array_to_delete_true = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, true);

        $writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN, self::VERBOSE]]);

        $this->assertEquals($settings_array_to_delete_true, $writers);
	}

	public function test_givenALogWriterConfigSettingsArrayWithThreeDeactivateWithFlagsAndLogFlagsHavingTwoUnallowed_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayMarkedToDelete(){
        $settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addDeactivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::VERBOSE, self::DEBUG, self::DRY_RUN]);
        $settings_array_to_delete_true = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, true);

        $writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN, self::VERBOSE]]);

        $this->assertEquals($settings_array_to_delete_true, $writers);
	}

	public function test_givenALogWriterConfigSettingsArrayWithTwoActivateWithFlagsAndLogFlagsHavingOneAllowed_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayNotMarkedToDelete(){
        $settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::VERBOSE, self::DEBUG]);
        $settings_array_to_delete_false = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, false);

        $writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$settings_array, [self::VERBOSE]]);

        $this->assertEquals($settings_array_to_delete_false, $writers);
	}

	public function test_givenALogWriterConfigSettingsArrayWithTwoActivateWithFlagsAndLogFlagsHavingOneAllowedAndOneUnallowed_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayNotMarkedToDelete(){
        $settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::VERBOSE, self::DEBUG]);
        $settings_array_to_delete_false = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, false);

        $writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN, self::VERBOSE]]);

        $this->assertEquals($settings_array_to_delete_false, $writers);
	}

	public function test_givenALogWriterConfigSettingsArrayWithTwoActivateWithFlagsAndLogFlagsHavingOneUnallowed_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayMarkedToDelete(){
        $settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::VERBOSE, self::DEBUG]);
        $settings_array_to_delete_true = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, true);

        $writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN]]);

        $this->assertEquals($settings_array_to_delete_true, $writers);
	}

	// else if(!empty($config_activate_with_flags) && !empty($config_deactivate_with_flags))
	public function test_givenALogWriterConfigSettingsArrayWithBothActivateAndDeactivateWithFlagsAndAndAllowedLogFlag_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayNotMarkedToDelete(){
        $settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::DRY_RUN]);
        $settings_array = $this->addDeactivateWithFlagOptions($settings_array, [self::DEBUG]);
        $settings_array_to_delete_false = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, false);

        $writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN]]);

        $this->assertEquals($settings_array_to_delete_false, $writers);
	}

	public function test_givenALogWriterConfigSettingsArrayWithBothActivateAndDeactivateWithFlagsAndAndUnallowedLogFlag_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayMarkedToDelete(){
        $settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::DRY_RUN]);
        $settings_array = $this->addDeactivateWithFlagOptions($settings_array, [self::DEBUG]);
        $settings_array_to_delete_true = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, true);

        $writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DEBUG]]);

        $this->assertEquals($settings_array_to_delete_true, $writers);
	}

	public function test_givenALogWriterConfigSettingsArrayWithOneActivateWithFlagAndTwoDeactivateWithFlagndUnallowedLogFlag_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayMarkedToDelete(){
        $settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::DRY_RUN]);
        $settings_array = $this->addDeactivateWithFlagOptions($settings_array, [self::VERBOSE, self::DEBUG]);
        $settings_array_to_delete_true = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, true);

        $writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DEBUG]]);

        $this->assertEquals($settings_array_to_delete_true, $writers);
	}

	public function test_givenALogWriterConfigSettingsArrayWithOneActivateWithFlagAndTwoDeactivateWithFlagAndOneAllowedLogFlagAndOneUnallowedLogFlag_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayMarkedToDelete(){
        $settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::DRY_RUN]);
        $settings_array = $this->addDeactivateWithFlagOptions($settings_array, [self::VERBOSE, self::DEBUG]);
        $settings_array_to_delete_true = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, true);

        $writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN, self::DEBUG]]);

        $this->assertEquals($settings_array_to_delete_true, $writers);
	}

	public function test_givenALogWriterConfigSettingsArrayWithOneActivateWithFlagAndThreeDeactivateWithFlagWithOneBeingTheSameAsAllowed_markUnallowedWritersToDelete_shouldReturnALogWritersSettingsArrayMarkedToDelete(){
        $settings_formatter = new SettingsFormatter($this->givenAConfigStruct());

        $settings_array = $this->addActivateWithFlagOptions(self::BASIC_LOG_WRITERS_CONFIG_SETTINGS, [self::DRY_RUN]);
        $settings_array = $this->addDeactivateWithFlagOptions($settings_array, [self::VERBOSE, self::DEBUG, self::DRY_RUN]);
        $settings_array_to_delete_true = $this->addToDeleteMarkerToLogConfigSettingsArray($settings_array, true);

        $writers = $this->invokeMethod($settings_formatter, 'markUnallowedWritersToDelete', [$settings_array, [self::DRY_RUN]]);

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

	private function thenAnArrayOfActiveLogFlag(){
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