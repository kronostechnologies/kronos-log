<?php

namespace Kronos\Log\Enumeration;

use Kronos\Log\Enumeration;

class WriterTypes extends Enumeration {
	const CONSOLE = 'console';
	const FILE = 'file';
	const LOGDNA = 'logdna';
	const MEMORY = 'memory';
	const SENTRY = 'sentry';
	const SYSLOG = 'syslog';
}