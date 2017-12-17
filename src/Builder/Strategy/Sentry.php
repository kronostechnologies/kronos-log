<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy;
use Kronos\Log\Exception\InvalidSetting;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\Writer As WriterFactory;
use SebastianBergmann\GlobalState\RuntimeException;

class Sentry extends AbstractWriter {

	const CLIENT = 'client';

	const KEY = 'key';
	const SECRET = 'secret';
	const PROJECT_ID = 'projectId';
	const OPTIONS = 'options';

	/**
	 * @var WriterFactory
	 */
	private $factory;

	public function __construct(WriterFactory $factory = null) {
		$this->factory = is_null($factory) ? new WriterFactory() : $factory;
	}

	/**
	 * @param array $settings
	 * @return \Kronos\Log\Writer\Sentry
	 * @throws InvalidSetting
	 * @throws RequiredSetting
	 */
	public function buildFromArray(array $settings, $context) {
		if(isset($settings[self::CLIENT]) && $settings[self::CLIENT]) {
			if($settings[self::CLIENT] instanceof \Raven_Client) {
				$writer = $this->factory->createSentryWriter($settings[self::CLIENT]);
			}
			else {
				throw new InvalidSetting(self::CLIENT.' setting must be an instance of Raven_Client, instance of '.get_class($settings[self::CLIENT]).' given');
			}
		}
		else if(isset($settings[self::KEY])) {
			if(!isset($settings[self::SECRET])) {
				throw new RequiredSetting(self::SECRET.' setting is required with '.self::KEY);
			}
			else if(!isset($settings[self::PROJECT_ID])) {
				throw new RequiredSetting(self::PROJECT_ID.' setting is required with '.self::KEY);
			}
			else {
				$writer = $this->factory->createSentryWriterAndRavenClient($settings[self::KEY], $settings[self::SECRET], $settings[self::PROJECT_ID], $this->getOptions($settings));
			}
		}
		else {
			throw new RequiredSetting(self::CLIENT.' setting or '.self::KEY.' setting must given');
		}

		$this->setCommonSettings($writer, $settings);

        $writer->setConfigContext($context);

		return $writer;
	}

	/**
	 * @param array $settings
	 * @return array
	 */
	private function getOptions(array $settings) {
		return isset($settings[self::OPTIONS]) ? $settings[self::OPTIONS] : [];
	}
}