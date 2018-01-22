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

	/**
	 * @var array
	 */
	private $tool_log_modes = [];

	const WRITER_TYPE = 'type';
	const WRITER_SETTINGS = 'settings';
	const TO_DELETE = 'to_delete';
	const ACTIVATE_ONLY_WITH_FLAG = 'activateOnlyWithFlag';
	const DEACTIVATE_ONLY_WITH_FLAG = 'deactivateOnlyWithFlag';

	/**
	 * SettingsFormatter constructor.
	 *
	 * @param array $settings
	 * @param array $changes
	 */
	public function __construct(array $settings = [], array $changes = [], array $tool_log_modes = []) {
		$this->settings = $settings;
		$this->changes = $changes;
		$this->tool_log_modes = $tool_log_modes;
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
					foreach($writers as $key => &$writer) {

						if (!empty($this->tool_log_modes)) {
							$this->markUnallowedWritersToDelete($writers, $writer, $key);
						}

						$this->deleteMarkedWritersToDelete($writers);

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

	/**
	 * Returns a tuple of log modes arrays separated by whether or not they are activated.
	 *
	 * @return array
	 */
	private function getToolCurrentLogModesTuple(){
		$active_modes = [];
		$inactive_modes = [];

		foreach($this->tool_log_modes as $log_mode => $is_activated){
			if ($is_activated){
				$active_modes[] = $log_mode;
			}
			else if (!$is_activated){
				$inactive_modes[] = $log_mode;
			}
		}

		return [
			'active_modes' => $active_modes,
			'inactive_modes' => $inactive_modes
		];
	}

	/**
	 * Takes the current log mode setup of a tool and compares it to the activateOnlyWith and deactivateOnlyWith flags in the settings.
	 *
	 * @param $writers
	 * @param $writer
	 * @param $key
	 */
	private function markUnallowedWritersToDelete(&$writers, &$writer, $key){
		$writers[$key][self::TO_DELETE] = false;

		$tool_log_modes = $this->getToolCurrentLogModesTuple();

		$activate_array = $writer[self::WRITER_SETTINGS][self::ACTIVATE_ONLY_WITH_FLAG] ?: [];
		$deactivate_array = $writer[self::WRITER_SETTINGS][self::DEACTIVATE_ONLY_WITH_FLAG] ?: [];

		foreach($tool_log_modes as $tool_log_mode_name => $tool_log_mode){
			$this->markToDelete($writers, $key, $tool_log_mode, ($tool_log_mode_name == 'active_modes') ? $activate_array : $deactivate_array);
		}
	}

	/**
	 * MArks writers for deletion.
	 *
	 * @param $writers
	 * @param $key
	 * @param $tool_log_modes
	 * @param $config_log_modes
	 */
	private function markToDelete(&$writers, $key, $tool_log_modes, $config_log_modes){
		if (!empty($tool_log_modes)){
			foreach ($tool_log_modes as $tool_log_mode){
				if (in_array($tool_log_mode, $config_log_modes)){
					$writers[$key][self::TO_DELETE] = true;
				}
			}
		}
	}

	/**
	 * Delete writers marked for deletion.
	 *
	 * @param $writers
	 * @param $key
	 */
	private function deleteMarkedWritersToDelete(&$writers){
		foreach ($writers  as $key => $writer){
			if (isset($writer[self::TO_DELETE]) && $writer[self::TO_DELETE]){
				unset($writers[$key]);
			}
		}
	}
}