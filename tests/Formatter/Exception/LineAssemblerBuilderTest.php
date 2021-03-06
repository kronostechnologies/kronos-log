<?php

namespace Kronos\Tests\Log\Formatter\Exception;

use Kronos\Log\Formatter\Exception\Factory;
use Kronos\Log\Formatter\Exception\LineAssembler;
use Kronos\Log\Formatter\Exception\LineAssemblerBuilder;
use Kronos\Log\Formatter\Exception\NamespaceShrinker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LineAssemblerBuilderTest extends TestCase
{
    const BASE_PATH = "/base/path";
    const INCLUDE_ARGS = true;

    /**
     * @var Factory&MockObject
     */
    private $factory;

    /**
     * @var LineAssembler
     */
    private $lineAssembler;

    /**
     * @var NamespaceShrinker
     */
    private $namespaceShrinker;

    /**
     * @var LineAssemblerBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(Factory::class);
        $this->lineAssembler = $this->createMock(LineAssembler::class);
        $this->namespaceShrinker = $this->createMock(NamespaceShrinker::class);
        $this->factory->method('createNamespaceShrinker')->willReturn($this->namespaceShrinker);

        $this->builder = new LineAssemblerBuilder($this->factory);
    }

    public function test_builder_buildAssembler_shouldCreateNamespaceShrinker(): void
    {
        $this->factory
            ->expects(self::once())
            ->method('createNamespaceShrinker');

        $this->builder->buildAssembler();
    }

    public function test_namespaceShrinker_buildAssembler_shouldCreateAndReturnLineAssembler(): void
    {
        $this->factory
            ->expects(self::once())
            ->method('createLineAssembler')
            ->with($this->namespaceShrinker)
            ->willReturn($this->lineAssembler);
        $this->lineAssembler
            ->expects(self::never())
            ->method('stripBasePath');

        $actualAssembler = $this->builder->buildAssembler();

        $this->assertSame($this->lineAssembler, $actualAssembler);
    }

    public function test_includeArgs_buildAssembler_shouldIncludeArgsOnLineAssembler(): void
    {
        $this->givenLineAssembler();
        $this->lineAssembler
            ->expects(self::once())
            ->method('includeArgs')
            ->with(self::INCLUDE_ARGS);
        $this->builder->includeArgs(self::INCLUDE_ARGS);

        $this->builder->buildAssembler();
    }

    public function test_stripBasePath_buildAssembler_shouldStripBasePathOnLineAssembler(): void
    {
        $this->givenLineAssembler();
        $this->lineAssembler
            ->expects(self::once())
            ->method('stripBasePath')
            ->with(self::BASE_PATH);
        $this->builder->stripBasePath(self::BASE_PATH);

        $this->builder->buildAssembler();
    }

    public function test_shrinkPath_buildAssembler_shouldShrinkPathOnLineAssembler(): void
    {
        $this->givenLineAssembler();
        $this->lineAssembler
            ->expects(self::once())
            ->method('shrinkPaths')
            ->with(true);
        $this->builder->shrinkPaths(true);

        $this->builder->buildAssembler();
    }

    public function test_removeExtension_buildAssembler_shouldRemoveExtensionOnLineAssembler(): void
    {
        $this->givenLineAssembler();
        $this->lineAssembler
            ->expects(self::once())
            ->method('removeExtension')
            ->with(true);
        $this->builder->removeExtension(true);

        $this->builder->buildAssembler();
    }

    public function test_shrinkNamespaces_buildAssembler_shouldShrinkNamespacesOnLineAssembler(): void
    {
        $this->givenLineAssembler();
        $this->lineAssembler
            ->expects(self::once())
            ->method('shrinkNamespaces')
            ->with(true);
        $this->builder->shrinkNamespaces(true);

        $this->builder->buildAssembler();
    }

    protected function givenLineAssembler(): void
    {
        $this->factory
            ->method('createLineAssembler')
            ->willReturn($this->lineAssembler);
    }
}
