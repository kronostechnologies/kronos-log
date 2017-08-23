<?php

namespace Kronos\Log\Adaptor;

class FileFactory {
	public function createFileAdaptor($filename) {
		return new File($filename);
	}

	public function createTTYAdaptor($filename) {
		return new TTY($filename);
	}
}