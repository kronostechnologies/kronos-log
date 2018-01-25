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
	const ACTIVATE_WITH_FLAG = 'activateWithFlag';
	const DEACTIVATE_WITH_FLAG = 'deactivateWithFlag';
	const ACTIVE_MODES = 'active_modes';

	/**
	 * SettingsFormatter constructor.
	 *
	 * @param array $settings
	 * @param array $changes
	 * @param array $tool_log_modes
	 */
	public function __construct(array $settings = [], array $changes = [], array $tool_log_modes = []) {
		$this->settings = $settings;
		$this->changes = $changes;
		$this->tool_log_modes = $tool_log_modes;
	}

	/**
	 * Returns a formatted array of settings for the log writer builder.
	 *
	 * @return array
	 */
	public function getFormattedSettings(){
		$writers = $this->getWriters();

		if (!empty($writers)){
			$active_tool_log_modes = $this->getActiveToolLogModes();
			$writers = $this->markUnallowedWritersToDelete($writers, $active_tool_log_modes);
			$writers = $this->deleteMarkedWritersToDelete($writers);

			if (!empty($this->tool_log_modes)) {
				$writers = $this->setIncludeDebugLevelForWriters($writers);
			}

			$writers = $this->formatWritersSettings($writers);
		}

		return $writers;
	}

	/**
	 * Uses the changes array to modify or add to the log settings.
	 *
	 * @param $writers
	 * @return mixed
	 */
	private function formatWritersSettings($writers){
		$writers_array = $writers;

		if (!empty($this->changes)){
			foreach ($this->changes as $writer_type => $writer_settings){
				if (!empty($this->settings)){
					foreach($writers_array as $key => $writer) {
						if ($writer[self::WRITER_TYPE] == $writer_type){
							foreach ($writer_settings as $setting_name => $setting_value){
								$writers_array[$key][self::WRITER_SETTINGS][$setting_name] = $setting_value;
							}
						}
					}
				}
			}
		}

		return $writers_array;
	}

	/**
	 * If the 'debug' tool log mode is set, include the 'DEBUG' LogLevel in the writer
	 *
	 * @param $writer
	 * @param $tool_debug_value
	 */
	private function setIncludeDebugLevelForWriters($writers){
		$writers_array = $writers;

		foreach ($writers_array as $key => $writer){
			if ($this->tool_log_modes['debug']){
				$writers_array[$key][self::WRITER_SETTINGS]['includeDebugLevel'] = true;
			}
		}

		return $writers_array;
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
	 * Returns an array of active log modes..
	 *
	 * @return array
	 */
	private function getActiveToolLogModes(){
		$active_modes = [];

		foreach($this->tool_log_modes as $log_mode => $is_activated){
			if ($is_activated){
				$active_modes[] = $log_mode;
			}
		}

		return $active_modes;
	}

	/**
	 * Compares active and inactive tool_log_modes (verbose/debug/dry-run) with allowed and unallowed modes in each of the writers' config.
	 *
	 * @param $writers
	 * @param $active_tool_log_modes
	 * @return mixed
	 */
	private function markUnallowedWritersToDelete($writers, $active_tool_log_modes){
		$writers_array = $writers;

		foreach ($writers_array as $key => $writer){
			$config_activate_with_flags = $writer[self::WRITER_SETTINGS][self::ACTIVATE_WITH_FLAG];
			$config_deactivate_with_flags = $writer[self::WRITER_SETTINGS][self::DEACTIVATE_WITH_FLAG];
			$to_delete = false;

			if (empty($config_activate_with_flags) && empty($config_deactivate_with_flags)){
				continue;
			}
			else if(empty($config_activate_with_flags) && !empty($config_deactivate_with_flags)){
				if ($this->isAtLeastOneToolLogModeInConfigFlag($active_tool_log_modes, $config_deactivate_with_flags)){
					$to_delete = true;
				}
			}
			else if (!empty($config_activate_with_flags) && empty($config_deactivate_with_flags)){
				if ($this->isToolLogModesNotInConfigFlag($active_tool_log_modes, $config_activate_with_flags)){
					$to_delete = true;
				}
			}
			else if(!empty($config_activate_with_flags) && !empty($config_deactivate_with_flags)){
				if ($this->isAtLeastOneToolLogModeInConfigFlag($active_tool_log_modes, $config_deactivate_with_flags)
					|| $this->isToolLogModesNotInConfigFlag($active_tool_log_modes, $config_activate_with_flags)){
					$to_delete = true;
				}
			}

			$writers_array[$key][self::TO_DELETE] = $to_delete;
		}

		return $writers_array;
	}

	/**
	 * Checks if at least one of the current tool log modes is in the activate/deactivate flags array
	 *
	 * @param $tool_log_mode
	 * @param $config_flag
	 * @return bool
	 */
	private function isAtLeastOneToolLogModeInConfigFlag($tool_log_mode, $config_flag){
		return count(array_intersect($tool_log_mode, $config_flag)) > 0;
	}

	/**
	 * Checks if none of the current tool log modes is in the activate/deactivate flags array
	 *
	 * @param $tool_log_mode
	 * @param $config_flag
	 * @return bool
	 */
	private function isToolLogModesNotInConfigFlag($tool_log_mode, $config_flag){
		return count(array_intersect($tool_log_mode, $config_flag)) == 0;
	}

	/**
	 * Delete writers marked for deletion.
	 *
	 * @param $writers
	 * @param $key
	 */
	private function deleteMarkedWritersToDelete($writers){
		$writers_array = $writers;

		foreach ($writers_array  as $key => $writer){
			if (isset($writer[self::TO_DELETE]) && $writer[self::TO_DELETE]){
				unset($writers_array[$key]);
			}
		}

		return $writers_array;
	}
}