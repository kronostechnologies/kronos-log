<?php

namespace Kronos\Tests\Log;

use Kronos\Log\Enumeration\WriterTypes;
use Kronos\Log\SettingsFormatter;

class SettingsFormatterTest extends \PHPUnit_Framework_TestCase
{
    const FIRST_WRITER_TYPE = WriterTypes::CONSOLE;
    const SECOND_WRITER_TYPE = WriterTypes::FILE;
    const SETTING_NAME = 'setting name';
    const SETTING_VALUE = 'setting value';
    const ACTIVATION_FLAG = 'activation flag';
    const DEACTIVATION_FLAG = 'deactivation flag';
    const ORIGINAL_SETTING_VALUE = 'original setting value';
    const GLOBAL_SETTING_NAME = 'global setting name';
    const NEW_VALUE = 'new value';
    const GLOBAL_VALUE = 'global value';

    const DEFAULT_SETTING_NAME = 'default setting name';

    public function test_Settings_getFormattedSettings_ShouldReturnSettings()
    {
        $expectedSettings = [
            [
                SettingsFormatter::WRITER_TYPE => self::FIRST_WRITER_TYPE,
                SettingsFormatter::WRITER_SETTINGS => [
                    self::SETTING_NAME => self::SETTING_VALUE
                ]
            ]
        ];
        $formatter = new SettingsFormatter($expectedSettings);

        $actualSettings = $formatter->getFormattedSettings();

        $this->assertEquals($expectedSettings, $actualSettings);
    }

    public function test_WriterWithActivationFlag_getFormattedSettings_ShouldRemoveWriterFromSettings()
    {
        $settings = [
            [
                SettingsFormatter::WRITER_TYPE => self::FIRST_WRITER_TYPE,
                SettingsFormatter::WRITER_SETTINGS => [
                    self::SETTING_NAME => self::SETTING_VALUE,
                    SettingsFormatter::ACTIVATE_WITH_FLAG => [self::ACTIVATION_FLAG]
                ]
            ]
        ];
        $formatter = new SettingsFormatter($settings);

        $actualSettings = $formatter->getFormattedSettings();

        $this->assertEquals([], $actualSettings);
    }

    public function test_WriterWithActivationFlagAndFlagSet_getFormattedSettings_ShouldKeepWriterInSettings()
    {
        $expectedSettings = [
            [
                SettingsFormatter::WRITER_TYPE => self::FIRST_WRITER_TYPE,
                SettingsFormatter::WRITER_SETTINGS => [
                    self::SETTING_NAME => self::SETTING_VALUE,
                    SettingsFormatter::ACTIVATE_WITH_FLAG => [self::ACTIVATION_FLAG]
                ]
            ]
        ];
        $formatter = new SettingsFormatter($expectedSettings);
        $formatter->setFlags([self::ACTIVATION_FLAG]);

        $actualSettings = $formatter->getFormattedSettings();

        $this->assertEquals($expectedSettings, $actualSettings);
    }

    public function test_WriterWithDeactivationFlag_getFormattedSettings_ShouldKeepWriterInSettings()
    {
        $expectedSettings = [
            [
                SettingsFormatter::WRITER_TYPE => self::FIRST_WRITER_TYPE,
                SettingsFormatter::WRITER_SETTINGS => [
                    self::SETTING_NAME => self::SETTING_VALUE,
                    SettingsFormatter::DEACTIVATE_WITH_FLAG => [self::DEACTIVATION_FLAG]
                ]
            ]
        ];
        $formatter = new SettingsFormatter($expectedSettings);

        $actualSettings = $formatter->getFormattedSettings();

        $this->assertEquals($expectedSettings, $actualSettings);
    }

    public function test_WriterWithDeactivationFlagAndFlagSet_getFormattedSettings_ShouldRemoveWriterFromSettings()
    {
        $settings = [
            [
                SettingsFormatter::WRITER_TYPE => self::FIRST_WRITER_TYPE,
                SettingsFormatter::WRITER_SETTINGS => [
                    self::SETTING_NAME => self::SETTING_VALUE,
                    SettingsFormatter::DEACTIVATE_WITH_FLAG => [self::DEACTIVATION_FLAG]
                ]
            ]
        ];
        $formatter = new SettingsFormatter($settings);
        $formatter->setFlags([self::DEACTIVATION_FLAG]);

        $actualSettings = $formatter->getFormattedSettings();

        $this->assertEquals([], $actualSettings);
    }

    public function test_WriterWithBothActivationAndDeactivationFlagAndFlagsSet_getFormattedSettings_ShouldRemoveWriterFromSettings(
    )
    {
        $expectedSettings = [
            [
                SettingsFormatter::WRITER_TYPE => self::FIRST_WRITER_TYPE,
                SettingsFormatter::WRITER_SETTINGS => [
                    self::SETTING_NAME => self::SETTING_VALUE,
                    SettingsFormatter::ACTIVATE_WITH_FLAG => [self::ACTIVATION_FLAG],
                    SettingsFormatter::DEACTIVATE_WITH_FLAG => [self::DEACTIVATION_FLAG]
                ]
            ]
        ];
        $formatter = new SettingsFormatter($expectedSettings);
        $formatter->setFlags([self::ACTIVATION_FLAG, self::DEACTIVATION_FLAG]);

        $actualSettings = $formatter->getFormattedSettings();

        $this->assertEquals([], $actualSettings);
    }

    public function test_GlobalChanges_getFormattedSettings_ShouldChangeSettingsForAllWriters()
    {
        $settings = [
            [
                SettingsFormatter::WRITER_TYPE => self::FIRST_WRITER_TYPE,
                SettingsFormatter::WRITER_SETTINGS => [
                    self::SETTING_NAME => self::SETTING_VALUE,
                    self::GLOBAL_SETTING_NAME => self::ORIGINAL_SETTING_VALUE
                ]
            ],
            [
                SettingsFormatter::WRITER_TYPE => self::SECOND_WRITER_TYPE,
            ]
        ];
        $expectedSettings = $settings; // clone array
        $expectedSettings[0][SettingsFormatter::WRITER_SETTINGS][self::GLOBAL_SETTING_NAME] = self::NEW_VALUE;
        $expectedSettings[1][SettingsFormatter::WRITER_SETTINGS] = [];
        $expectedSettings[1][SettingsFormatter::WRITER_SETTINGS][self::GLOBAL_SETTING_NAME] = self::NEW_VALUE;
        $formatter = new SettingsFormatter($settings);
        $formatter->setGlobalChanges([self::GLOBAL_SETTING_NAME => self::NEW_VALUE]);

        $actualSettings = $formatter->getFormattedSettings();

        $this->assertEquals($expectedSettings, $actualSettings);
    }

    public function test_WriterSpecificChanges_getFormattedSettings_ShouldChangeSettingsForSpecifiedWriters()
    {
        $settings = [
            [
                SettingsFormatter::WRITER_TYPE => self::FIRST_WRITER_TYPE,
                SettingsFormatter::WRITER_SETTINGS => [
                    self::SETTING_NAME => self::ORIGINAL_SETTING_VALUE,
                ]
            ],
            [
                SettingsFormatter::WRITER_TYPE => self::SECOND_WRITER_TYPE,
                SettingsFormatter::WRITER_SETTINGS => [
                    self::SETTING_NAME => self::ORIGINAL_SETTING_VALUE,
                ]
            ]
        ];
        $expectedSettings = $settings; // clone array
        $expectedSettings[0][SettingsFormatter::WRITER_SETTINGS][self::SETTING_NAME] = self::NEW_VALUE;
        $formatter = new SettingsFormatter($settings);
        $formatter->setWriterSpecificChanges([self::FIRST_WRITER_TYPE => [self::SETTING_NAME => self::NEW_VALUE]]);

        $actualSettings = $formatter->getFormattedSettings();

        $this->assertEquals($expectedSettings, $actualSettings);
    }

    public function test_GlobalAndWriterSpecificChanges_getFormattedSettings_ShouldKeepSpecificValue()
    {
        $settings = [
            [
                SettingsFormatter::WRITER_TYPE => self::FIRST_WRITER_TYPE,
                SettingsFormatter::WRITER_SETTINGS => [
                    self::SETTING_NAME => self::ORIGINAL_SETTING_VALUE,
                ]
            ]
        ];
        $expectedSettings = $settings; // clone array
        $expectedSettings[0][SettingsFormatter::WRITER_SETTINGS][self::SETTING_NAME] = self::NEW_VALUE;
        $formatter = new SettingsFormatter($settings);
        $formatter->setWriterSpecificChanges([self::FIRST_WRITER_TYPE => [self::SETTING_NAME => self::NEW_VALUE]]);
        $formatter->setGlobalChanges([self::SETTING_NAME => self::GLOBAL_VALUE]);

        $actualSettings = $formatter->getFormattedSettings();

        $this->assertEquals($expectedSettings, $actualSettings);
    }

    public function test_DefaultSettings_getFormattedSettings_ShouldSetSettingIfNotSpeficied()
    {
        $settings = [
            [
                SettingsFormatter::WRITER_TYPE => self::FIRST_WRITER_TYPE,
                SettingsFormatter::WRITER_SETTINGS => [
                    self::SETTING_NAME => self::SETTING_VALUE,
                    self::DEFAULT_SETTING_NAME => self::ORIGINAL_SETTING_VALUE
                ]
            ],
            [
                SettingsFormatter::WRITER_TYPE => self::SECOND_WRITER_TYPE,
                SettingsFormatter::WRITER_SETTINGS => [
                    self::SETTING_NAME => self::SETTING_VALUE,
                ]
            ]
        ];
        $expectedSettings = $settings; // clone array
        $expectedSettings[1][SettingsFormatter::WRITER_SETTINGS][self::DEFAULT_SETTING_NAME] = self::NEW_VALUE;
        $formatter = new SettingsFormatter($settings);
        $formatter->setDefaults([self::DEFAULT_SETTING_NAME => self::NEW_VALUE]);

        $actualSettings = $formatter->getFormattedSettings();

        $this->assertEquals($expectedSettings, $actualSettings);
    }
}