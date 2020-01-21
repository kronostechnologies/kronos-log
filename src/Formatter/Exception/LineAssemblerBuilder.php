<?php

namespace Kronos\Log\Formatter\Exception;

class LineAssemblerBuilder
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var bool
     */
    private $includeArgs = false;

    /**
     * @var string
     */
    private $stripBasePath = '';

    /**
     * @var bool
     */
    private $removeFileExtension = false;

    /**
     * @var bool
     */
    private $shrinkNamespaces = false;

    /**
     * @var bool
     */
    private $shrinkPaths = false;

    /**
     * @var NamespaceShrinker
     */
    private $namespaceShrinker;

    /**
     * LineAssemblerBuilder constructor.
     * @param Factory|null $factory
     */
    public function __construct(Factory $factory = null)
    {
        $this->factory = $factory ?? new Factory();
    }

    /**
     * @param bool $includeArgs
     * @return LineAssemblerBuilder
     */
    public function includeArgs(bool $includeArgs): self
    {
        $this->includeArgs = $includeArgs;

        return $this;
    }

    /**
     * @param string $stripBasePath
     * @return LineAssemblerBuilder
     */
    public function stripBasePath(string $stripBasePath): self
    {
        $this->stripBasePath = $stripBasePath;

        return $this;
    }

    /**
     * @param bool $removeExtension
     * @return LineAssemblerBuilder
     */
    public function removeExtension(bool $removeExtension): self
    {
        $this->removeFileExtension = $removeExtension;

        return $this;
    }

    /**
     * @param bool $shrink
     * @return $this
     */
    public function shrinkNamespaces(bool $shrink): self
    {
        $this->shrinkNamespaces = $shrink;

        return $this;
    }

    /**
     * @param bool $shrink
     * @return $this
     */
    public function shrinkPaths(bool $shrink): self
    {
        $this->shrinkPaths = $shrink;

        return $this;
    }

    public function buildAssembler(): LineAssembler
    {
        $this->createNamespaceShrinkIfEnabled();

        $assembler = $this->factory->createLineAssembler($this->namespaceShrinker);
        $this->setupAssembler($assembler);
        return $assembler;
    }

    protected function createNamespaceShrinkIfEnabled(): void
    {
        if ($this->namespaceShrinker === null) {
            $this->namespaceShrinker = $this->factory->createNamespaceShrinker();
        }
    }

    /**
     * @param LineAssembler $assembler
     */
    protected function setupAssembler(LineAssembler $assembler): void
    {
        $assembler->includeArgs($this->includeArgs);
        if ($this->stripBasePath) {
            $assembler->stripBasePath($this->stripBasePath);
        }
        $assembler->shrinkPaths($this->shrinkPaths);
        $assembler->removeExtension($this->removeFileExtension);
        $assembler->shrinkNamespaces($this->shrinkNamespaces);
    }
}
