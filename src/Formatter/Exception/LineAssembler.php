<?php

namespace Kronos\Log\Formatter\Exception;

/**
 * Class LineAssembler
 * @package Kronos\Log\Exception
 */
class LineAssembler
{

    /**
     * @var string
     */
    private $stripBasePath = '';

    /**
     * @var bool
     */
    private $shrinkPaths = false;

    /**
     * @var bool
     */
    private $removeExtension = false;

    /**
     * @var bool
     */
    private $includeArgs = false;

    /**
     * @var bool
     */
    private $shrinkNamespaces = false;

    /**
     * @var NamespaceShrinker|null
     */
    private $namespaceShrinker;

    /**
     * @var String
     */
    private $line_nb;

    /**
     * @var String
     */
    private $file;

    /**
     * @var String
     */
    private $line;

    /**
     * @var String
     */
    private $function;

    /**
     * @var String
     */
    private $class;

    /**
     * @var String
     */
    private $type;

    /**
     * @var array
     */
    private $args = [];

    const ARRAY_TYPE = 'Array';

    /**
     * LineAssembler constructor.
     * @param NamespaceShrinker|null $namespaceShrinker
     */
    public function __construct(?NamespaceShrinker $namespaceShrinker = null)
    {
        $this->namespaceShrinker = $namespaceShrinker;
    }

    /**
     * @param string $stripBasePath
     */
    public function stripBasePath(string $stripBasePath): void
    {
        $this->stripBasePath = $stripBasePath;
    }

    public function shrinkPaths(bool $shrink = true): void
    {
        $this->shrinkPaths = $shrink;
    }

    /**
     * @param bool $removeExtension
     */
    public function removeExtension(bool $removeExtension): void
    {
        $this->removeExtension = $removeExtension;
    }

    public function includeArgs(bool $include = true): void
    {
        $this->includeArgs = $include;
    }

    public function shrinkNamespaces(bool $shrink = true): void
    {
        $this->shrinkNamespaces = $shrink;
    }

    /**
     * @param String $line_nb
     */
    public function setLineNb($line_nb)
    {
        $this->line_nb = $line_nb;
    }

    /**
     * @param String $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @param String $line
     */
    public function setLine($line)
    {
        $this->line = $line;
    }

    /**
     * @param String $function
     */
    public function setFunction($function)
    {
        $this->function = $function;
    }

    /**
     * @param String $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @param String $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param array $args
     */
    public function setArgs($args)
    {
        $this->args = $args;
    }

    /**
     * Build the line string depending on the inputted elements
     *
     * @return string
     */
    public function buildExceptionString()
    {
        $traceLine = '';

        if (is_numeric($this->line_nb)) {
            $traceLine .= "#" . $this->line_nb . ' ';
        }

        if (!empty($this->file)) {
            $file = $this->file;
            if ($this->stripBasePath !== '' && strpos($file, $this->stripBasePath) === 0) {
                $file = substr($file, strlen($this->stripBasePath));
            }

            if ($this->removeExtension) {
                $pathinfo = pathinfo($file);
                $file = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'];
            }

            if ($this->shrinkPaths && $this->namespaceShrinker) {
                $file = $this->namespaceShrinker->shrinkUsingSeparator($file, DIRECTORY_SEPARATOR);
            }

            $traceLine .= $file;
        }

        if (!empty($this->line)) {
            $traceLine .= '(' . $this->line . '): ';
        }

        if (!empty($this->class)) {
            if ($this->shrinkNamespaces && $this->namespaceShrinker) {
                $traceLine .= $this->namespaceShrinker->shrink($this->class);
            } else {
                $traceLine .= $this->class;
            }
        }

        if (!empty($this->type)) {
            $traceLine .= $this->type;
        }

        // Function and arguments parts always together
        if (!empty($this->function)) {
            $traceLine .= $this->function . '(';

            if ($this->includeArgs && !empty($this->args)) {
                $arg_array = [];

                foreach ($this->args as $arg) {
                    if (is_array($arg)) {
                        $arg_array[] = self::ARRAY_TYPE;
                    } elseif (is_object($arg)) {
                        $arg_array[] = get_class($arg);
                    } else {
                        $arg_array[] = $arg;
                    }
                }

                $traceLine .= implode(',', $arg_array) . ')';
            } else {
                $traceLine .= ')';
            }
        }

        return $traceLine;
    }
}
