<?php

namespace Kronos\Log\Exception;

/**
 * Class LineBuilder
 * @package Kronos\Log\Exception
 */
class LineBuilder {

    /**
     * @var String
     */
    public $line_nb;

    /**
     * @var String
     */
    public $file;

    /**
     * @var String
     */
    public $line;

    /**
     * @var String
     */
    public $function;

    /**
     * @var String
     */
    public $class;

    /**
     * @var String
     */
    public $type;

    /**
     * @var array
     */
    public $args = [];

    /**
     * @var string
     */
    public $ex_line = "";

    const ARRAY_TYPE = 'Array';

    /**
     * @param String $line_nb
     */
    public function setLineNb($line_nb){
        $this->line_nb = $line_nb;
    }

    /**
     * @param String $file
     */
    public function setFile($file){
        $this->file = $file;
    }

    /**
     * @param String $line
     */
    public function setLine($line){
        $this->line = $line;
    }

    /**
     * @param String $function
     */
    public function setFunction($function){
        $this->function = $function;
    }

    /**
     * @param String $class
     */
    public function setClass($class){
        $this->class = $class;
    }

    /**
     * @param String $type
     */
    public function setType($type){
        $this->type = $type;
    }

    /**
     * @param array $args
     */
    public function setArgs($args){
        $this->args = $args;
    }

    /**
     * Build the line string depending on the inputted elements
     *
     * @return string
     */
    public function buildExceptionString() {

        if(is_numeric($this->line_nb) && !is_null($this->line_nb)) {
            $this->ex_line .= "#" . $this->line_nb . ' ';
        }

        if(!empty($this->file)) {
            $this->ex_line .= $this->file;
        }

        if(!empty($this->line)) {
            $this->ex_line .= '(' . $this->line . '): ';
        }

        if(!empty($this->class)) {
            $this->ex_line .= $this->class;
        }

        if(!empty($this->type)) {
            $this->ex_line .= $this->type;
        }

        if(!empty($this->function)) {
            $this->ex_line .= $this->function . '(';
        }

        if(!empty($this->args)) {
        	$arg_array = [];

        	foreach ($this->args as $arg){
				$arg_array[] = (is_array($arg) ? self::ARRAY_TYPE : $arg;
			}

            $this->ex_line .= implode(',', $arg_array) . ')';
        }
        else{
            $this->ex_line .= ')';
        }

        return $this->ex_line;
    }

    /**
     * Clears the current line so that it can be written over
     */
    public function clearLine(){
        $this->ex_line = "";
    }
}