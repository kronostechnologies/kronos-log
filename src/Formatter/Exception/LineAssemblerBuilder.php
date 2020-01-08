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
     * LineAssemblerBuilder constructor.
     * @param Factory|null $factory
     */
    public function __construct(Factory $factory = null)
    {
        $this->factory = $factory ?? new Factory();
    }

    /**
     * @param bool $includeArgs
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

    public function buildAssembler(): LineAssembler
    {
        $assembler = $this->factory->createLineAssembler();

        $assembler->includeArgs($this->includeArgs);
        if ($this->stripBasePath) {
            $assembler->stripBasePath($this->stripBasePath);
        }
        $assembler->removeExtension($this->removeFileExtension);

        return $assembler;
    }
}
