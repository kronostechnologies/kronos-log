<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\Writer As WriterFactory;

class File extends AbstractWriter {

	const FILENAME = 'filename';

	/**
	 * @var WriterFactory
	 */
	private $factory;

	public function __construct(WriterFactory $factory = null) {
		$this->factory = is_null($factory) ? new WriterFactory() : $factory;
	}

	/**
	 * @param array $settings
	 * @return \Kronos\Log\Writer\File
	 * @throws RequiredSetting
	 */
	public function buildFromArray(array $settings,$context) {
		if(!isset($settings[self::FILENAME])) {
			throw new RequiredSetting(self::FILENAME.' setting is required');
		}

		$writer = $this->factory->createFileWriter($settings[self::FILENAME]);

		$this->setCommonSettings($writer, $settings);

        $writer->setConfigContext($context);

		return $writer;
	}
}