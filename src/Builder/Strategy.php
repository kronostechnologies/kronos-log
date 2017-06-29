<?php

namespace Kronos\Log\Builder;

use Kronos\Log\AbstractWriter;

interface Strategy {

	/**
	 * @param array $settings
	 * @return AbstractWriter
	 */
	public function buildFromArray(array $settings);
}