<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Enumeration\WriterTypes;
use Kronos\Log\Exception\InvalidCustomWriter;
use Kronos\Log\Exception\UnsupportedType;
use Kronos\Log\Factory\Strategy;

class Selector {

	/**
	 * @var Strategy
	 */
	private $factory;

	/**
	 * Selector constructor.
	 * @param Strategy $factory
	 */
	public function __construct(Strategy $factory = null) {
		$this->factory = $factory ?: new Strategy();
	}

	/**
	 * @param string $type
	 * @return \Kronos\Log\Builder\Strategy
	 * @throws UnsupportedType
	 */
	public function getStrategyForType($type) {
		switch($type) {
			case WriterTypes::CONSOLE:
				return $this->factory->createConsoleStrategy();
			case WriterTypes::FILE:
				return $this->factory->createFileStrategy();
			case WriterTypes::LOGDNA:
				return $this->factory->createLogDNAStrategy();
			case WriterTypes::MEMORY:
				return $this->factory->createMemoryStrategy();
			case WriterTypes::SENTRY:
				return $this->factory->createSentryStrategy();
			case WriterTypes::SYSLOG:
				return $this->factory->createSyslogStrategy();
			default:
			    try {
                    $customStrategy = $this->factory->createCustomWriterStrategy();
                    return $customStrategy->getStrategyForClass($type);
                }
                catch(InvalidCustomWriter $exception) {
			        throw $exception;
                }
                catch(\Exception $exception) {
                    throw new UnsupportedType('Unsupported writer type : '.$type);
                }
		}
	}
}