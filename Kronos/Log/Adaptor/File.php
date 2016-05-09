<?php

namespace Kronos\Log\Adaptor;

class File {

	private $ressource;

	public function __construct($filename) {
		if(!file_exists($filename)) {
			throw new \Exception('File does not exists : '.$filename);
		}
		else {
			$this->open($filename);
		}
	}

	private function open($filename) {
		$this->ressource = fopen($filename, 'a');

		if(!$this->ressource) {
			throw new \Exception('Could not open file : '.$filename);
		}
	}

	public function write($line, $add_eol = true) {
		if(!$this->ressource) {
			throw new \Exception('No file opened, cannot write');
		}

		fwrite($this->ressource, $line.($add_eol ? "\n" : ''));
	}

	public function __destruct() {
		if($this->ressource) {
			@fclose($this->ressource);
		}
	}
}