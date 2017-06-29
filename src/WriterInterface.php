<?php

namespace Kronos\Log;

interface WriterInterface {

	public function canLogLevel($level);

	public function log($level, $message, array $context = []);
}