<?php

namespace Kronos\Tests\Log\Writer;

use Kronos\Log\ContextStringifier;
use Kronos\Log\Writer\File;
use Psr\Log\LogLevel;
use \Kronos\Log\Logger;

class FileTest extends \PHPUnit_Framework_TestCase {

	const A_FILENAME = '/path/to/file';
	const ANY_LOG_LEVEL = LogLevel::INFO;
	const A_MESSAGE = 'a message {key}';
	const CONTEXT_KEY = 'key';
	const CONTEXT_VALUE = 'value';
	const INTERPOLATED_MESSAGE = 'a message value';
	const INTERPOLATED_MESSAGE_WITH_LOG_LEVEL = 'INFO : a message value';
	const DATETIME_REGEX = '\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]';

	const EXCEPTION_MESSAGE = 'Some exception message';
	const EXCEPTION_STACKTRACE = "A few lines\nOf stacktrace";

	const STRINGIFIED_CONTEXT = 'stringified context';

	private $adaptor;
	private $factory;

	public function setUp() {
		$this->adaptor = $this->getMockBuilder(\Kronos\Log\Adaptor\File::class)->disableOriginalConstructor()->getMock();

		$this->factory = $this->getMock(\Kronos\Log\Adaptor\FileFactory::class);
	}

	public function test_NewWriter_Constructor_ShouldCreateNewFile() {
		$this->factory
			->expects($this->once())
			->method('createFileAdaptor')
			->with(self::A_FILENAME);

		$writer = new File(self::A_FILENAME, $this->factory);
	}

	public function test_WriteWithAdaptor_Log_ShouldCallWriteWithInterpolatedMessage() {
		$this->givenFactoryReturnAdaptor();
		$this->expectsWriteToByCalledOnceWith(self::INTERPOLATED_MESSAGE);
		$writer = new File(self::A_FILENAME, $this->factory);

		$writer->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
	}

	public function test_WriterPrependingLogLevelAndDateTime_Log_ShouldCallWriteWithMessagePrependedByDateTimeThenLogLevel() {
		$this->givenFactoryReturnAdaptor();
		$this->expectsWriteToByCalledOnceWith($this->matchesRegularExpression('/'.self::DATETIME_REGEX.' '.self::INTERPOLATED_MESSAGE_WITH_LOG_LEVEL.'/'));
		$writer = new File(self::A_FILENAME, $this->factory);
		$writer->setPrependLogLevel();
		$writer->setPrependDateTime();

		$writer->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, [self::CONTEXT_KEY => self::CONTEXT_VALUE]);
	}

	public function test_ContextContainingException_Log_ShouldWriteExceptionMessageAndStacktrace() {
		$this->givenFactoryReturnAdaptor();
		$this->expectsWriteToBeCalledWithConsecutive([
			[self::INTERPOLATED_MESSAGE],
			[File::EXCEPTION_TITLE_LINE],
			[self::EXCEPTION_MESSAGE],
			[$this->anything()] // Because we can't mock exceptions, can't be sure it's really the stacktrace...
		]);
		$writer = new File(self::A_FILENAME, $this->factory);
		$context = [
			self::CONTEXT_KEY => self::CONTEXT_VALUE,
			Logger::EXCEPTION_CONTEXT => new \Exception(self::EXCEPTION_MESSAGE)
		];

		$writer->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, $context);
	}

	public function test_ContextWithFakeException_Log_ShouldNotWriteException() {
		$this->givenFactoryReturnAdaptor();
		$this->expectsWriteToByCalledOnceWith(self::INTERPOLATED_MESSAGE);
		$writer = new File(self::A_FILENAME, $this->factory);
		$context = [
			self::CONTEXT_KEY => self::CONTEXT_VALUE,
			Logger::EXCEPTION_CONTEXT => self::CONTEXT_VALUE
		];

		$writer->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, $context);
	}
	
	public function test_Writer_SetContextStringify_ShouldExcludeExceptionKey() {
		$this->givenFactoryReturnAdaptor();
		$context_stringifier = $this->getMock(ContextStringifier::class);
		$context_stringifier
			->expects($this->once())
			->method('excludeKey')
			->with(Logger::EXCEPTION_CONTEXT);
		$writer = new File(self::A_FILENAME, $this->factory);

		$writer->setContextStringifier($context_stringifier);
	}

	public function test_WriterWithContextStringifier_Log_ShouldWriteStringifiedVersionOfContext() {
		$this->givenFactoryReturnAdaptor();
		$context = [
			self::CONTEXT_KEY => self::CONTEXT_VALUE
		];
		$this->expectsWriteToBeCalledWithConsecutive([
			[self::INTERPOLATED_MESSAGE],
			[File::CONTEXT_TITLE_LINE],
			[self::STRINGIFIED_CONTEXT]
		]);
		$context_stringifier = $this->getMock(ContextStringifier::class);
		$context_stringifier
			->expects($this->once())
			->method('stringify')
			->with($context)
			->willReturn(self::STRINGIFIED_CONTEXT);
		$writer = new File(self::A_FILENAME, $this->factory);
		$writer->setContextStringifier($context_stringifier);

		$writer->log(self::ANY_LOG_LEVEL, self::A_MESSAGE, $context);
	}

	private function givenFactoryReturnAdaptor() {
		$this->factory->method('createFileAdaptor')->willReturn($this->adaptor);
	}

	private function expectsWriteToByCalledOnceWith($line) {
		$this->adaptor
			->expects($this->once())
			->method('write')
			->with($line);
	}

	private function expectsWriteToBeCalledWithConsecutive(array $consecutive_args) {
		$method = $this->adaptor
			->expects($this->exactly(count($consecutive_args)))
			->method('write');
		call_user_func_array([$method, 'withConsecutive'], $consecutive_args);
	}
}