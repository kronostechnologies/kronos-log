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
	private $flags = [];

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
	 * @param array $flags
	 */
	public function __construct(array $settings = [], array $changes = [], array $flags = []) {
		$this->settings = $settings;
		$this->changes = $changes;
		$this->flags = $flags;
	}

	/**
	 * Returns a formatted array of settings for the log writer builder.
	 *
	 * @return array
	 */
	public function getFormattedSettings(){
		$writers = $this->getWriters();

		if (!empty($writers)){
			if (!empty($this->flags)) {
				$activeFlags = $this->getActiveFlags();
				$writers = $this->markUnallowedWritersToDelete($writers, $activeFlags);
				$writers = $this->deleteMarkedWritersToDelete($writers);
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
		$writersArray = $writers;

		if (!empty($this->changes)){
			foreach ($this->changes as $writerType => $writerSettings){
				if (!empty($this->settings)){
					foreach($writersArray as $key => $writer) {
						if ($writer[self::WRITER_TYPE] == $writerType){
							foreach ($writerSettings as $settingName => $settingValue){
								$writersArray[$key][self::WRITER_SETTINGS][$settingName] = $settingValue;
							}
						}
					}
				}
			}
		}

		return $writersArray;
	}

    /**
     * If the 'debug' tool log mode is set, include the 'DEBUG' LogLevel in the writer
     *
     * @param $writers
     * @return array
     */
	private function setIncludeDebugLevelForWriters($writers){
		$writersArray = $writers;

		foreach ($writersArray as $key => $writer){
			if ($this->flags['debug']){
				$writersArray[$key][self::WRITER_SETTINGS]['includeDebugLevel'] = true;
			}
		}

		return $writersArray;
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
	 * Returns an array of active flags..
	 *
	 * @return array
	 */
	private function getActiveFlags(){
		$activeFlags = [];

		foreach($this->flags as $flag => $isActivated){
			if ($isActivated){
				$activeFlags[] = $flag;
			}
		}

		return $activeFlags;
	}

	/**
	 * Compares active and inactive tool_log_modes (verbose/debug/dry-run) with allowed and unallowed modes in each of the writers' config.
	 *
	 * @param $writers
	 * @param $activeFlags
	 * @return mixed
	 */
	private function markUnallowedWritersToDelete($writers, $activeFlags){
		$writersArray = $writers;

		foreach ($writersArray as $key => $writer){
			$configActivatedWithFlags = isset($writer[self::WRITER_SETTINGS][self::ACTIVATE_WITH_FLAG]) ? $writer[self::WRITER_SETTINGS][self::ACTIVATE_WITH_FLAG] : [];
			$configDeactivatedWithFlags = isset($writer[self::WRITER_SETTINGS][self::DEACTIVATE_WITH_FLAG]) ? $writer[self::WRITER_SETTINGS][self::DEACTIVATE_WITH_FLAG] : [];
			$toDelete = false;

			if (empty($configActivatedWithFlags) && empty($configDeactivatedWithFlags)){
				continue;
			}
			else if(empty($configActivatedWithFlags) && !empty($configDeactivatedWithFlags)){
				if ($this->isAtLeastOneFlagInConfig($activeFlags, $configDeactivatedWithFlags)){
					$toDelete = true;
				}
			}
			else if (!empty($configActivatedWithFlags) && empty($configDeactivatedWithFlags)){
				if ($this->areAllFlagsNotInConfig($activeFlags, $configActivatedWithFlags)){
					$toDelete = true;
				}
			}
			else if(!empty($configActivatedWithFlags) && !empty($configDeactivatedWithFlags)){
				if ($this->isAtLeastOneFlagInConfig($activeFlags, $configDeactivatedWithFlags)
					|| $this->areAllFlagsNotInConfig($activeFlags, $configActivatedWithFlags)){
					$toDelete = true;
				}
			}

			$writersArray[$key][self::TO_DELETE] = $toDelete;
		}

		return $writersArray;
	}

	/**
	 * Checks if at least one of the active flags is in the activate/deactivate flags array
	 *
	 * @param $activeFlags
	 * @param $config
	 * @return bool
	 */
	private function isAtLeastOneFlagInConfig($activeFlags, $config){
		return count(array_intersect($activeFlags, $config)) > 0;
	}

	/**
	 * Checks if none of the active flags is in the activate/deactivate flags array
	 *
	 * @param $activeFlags
	 * @param $config
	 * @return bool
	 */
	private function areAllFlagsNotInConfig($activeFlags, $config){
		return count(array_intersect($activeFlags, $config)) == 0;
	}

    /**
     * Remove writers marked for deletion.
     *
     * @param $writers
     * @return array
     */
	private function deleteMarkedWritersToDelete($writers){
		$writersArray = $writers;

		foreach ($writersArray  as $key => $writer){
			if (isset($writer[self::TO_DELETE]) && $writer[self::TO_DELETE]){
				unset($writersArray[$key]);
			}
		}

		return $writersArray;
	}
}