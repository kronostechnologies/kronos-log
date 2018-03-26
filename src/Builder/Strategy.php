<?php

namespace Kronos\Log\Builder;

use Kronos\Log\AbstractWriter;
use Kronos\Log\Factory\Writer;

interface Strategy {

	/**
	 * @param array $settings
	 * @return AbstractWriter
	 */
	public function buildFromArray(array $settings);
}