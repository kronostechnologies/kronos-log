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
	 * @var null|\Tool
	 */
	private $tool = null;

	const WRITER_TYPE = 'type';

	const WRITER_SETTINGS = 'settings';

	/**
	 * SettingsFormatter constructor.
	 *
	 * @param array $settings
	 * @param array $changes
	 */
	public function __construct(array $settings = [], array $changes = [], \Tool $tool = null) {
		$this->settings = $settings;
		$this->changes = $changes;
		$this->tool = $tool;
	}

	/**
	 * @return array
	 */
	private function getToolCurrentLogModesTuple(){
		$active_modes = [];
		$inactive_modes = [];

		$current_log_modes = [
			'verbose' => $this->tool->isVerbose(),
			'dry-run' => $this->tool->isDryRun(),
			'debug' => $this->tool->isDebug()
		];

		foreach($current_log_modes as $log_mode => $is_activated){
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
	 * @param $writers
	 * @param $writer
	 * @param $key
	 */
	private function markUnallowedWritersToDelete(&$writers, &$writer, $key){
		$writers[$key]['to_delete'] = false;

		$tool_log_modes = $this->getToolCurrentLogModesTuple();

		$activate_array = $writer[self::WRITER_SETTINGS]['activateOnlyWithFlag'] ?: [];
		$deactivate_array = $writer[self::WRITER_SETTINGS]['deactivateOnlyWithFlag'] ?: [];

		foreach($tool_log_modes as $tool_log_mode_name => $tool_log_mode){
			$this->markToDelete($writers, $key, $tool_log_mode, ($tool_log_mode_name == 'active_modes') ? $activate_array : $deactivate_array);
		}
	}

	/**
	 * @param $writers
	 * @param $key
	 * @param $tool_log_modes
	 * @param $config_log_modes
	 */
	private function markToDelete(&$writers, $key, $tool_log_modes, $config_log_modes){
		if (!empty($tool_log_modes)){
			foreach ($tool_log_modes as $tool_log_mode){
				if (in_array($tool_log_mode, $config_log_modes)){
					$writers[$key]['to_delete'] = true;
				}
			}
		}
	}

	/**
	 * @param $writers
	 * @param $key
	 */
	private function deleteMarkedWritersToDelete(&$writers, $key){
		foreach ($writers as $writer){
			if ($writer['to_delete']){
				unset($writers[$key]);
			}
		}
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

						if (!is_null($this->tool)) {
							$this->markUnallowedWritersToDelete($writers, $writer, $key);
							$this->deleteMarkedWritersToDelete($writers, $key);
						}

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