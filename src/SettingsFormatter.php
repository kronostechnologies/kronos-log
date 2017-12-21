<?php

namespace Kronos\Log;

/**
 * Class SettingsFormatter
 * @package Kronos\Log
 */
class SettingsFormatter {

	/**
	 * @var array
	 */
	private $settings = [];

	/**
	 * @var array
	 */
	private $changes = [];

	const WRITER_TYPE = 'type';

	const WRITER_SETTINGS = 'settings';

	/**
	 * SettingsFormatter constructor.
	 *
	 * @param array $settings
	 * @param array $changes
	 */
	public function __construct(array $settings = [], array $changes = []) {
		$this->settings = $settings;
		$this->changes = $changes;
	}

	/**
	 * Uses the changes array to modify or add to the log settings
	 *
	 * @return array
	 */
	public function getFormattedSettings(){
		$writers = $this->getWriters();

		if (!empty($this->changes)){
			foreach ($this->changes as $writer_type => $writer_settings){
				if (!empty($this->settings)){
					foreach($writers as &$writer) {
						if ($writer[self::WRITER_TYPE] == $writer_type){
							foreach ($writer_settings as $setting_name => $setting_value){
								$writer[self::WRITER_SETTINGS][$setting_name] = $setting_value;
							}
						}
					}
				}
			}
		}

		return $writers;
	}

	/**
	 * Gets the writers options of the 'log' config settings
	 *
	 * @return array
	 */
	public function getWriters(){
		return (isset($this->settings['writers'])) ? $this->settings['writers'] : [];
	}

	/**
	 * Sets the writer settings to change, using an array of the format
	 *
	 * ['writer_name' => [
	 * 						'setting_name' => 'setting_value'
	 * 					]
	 * ]
	 *
	 * @param array $changes
	 */
	public function setChanges(array $changes){
		$this->changes = $changes;
	}
}