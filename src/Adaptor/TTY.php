<?php

namespace Kronos\Log\Adaptor;

use Kronos\Log\Enumeration\AnsiBackgroundColor;
use Kronos\Log\Enumeration\AnsiTextColor;

class TTY
{

    const ESCAPE_SEQUENCE = "\033[";
    const NO_COLOR = "\033[0m";
    const END_SEQUENCE = "m";

    private $ressource;

    private $force_ansi_color_support = false;
    private $force_no_ansi_color_support = false;


    public function __construct($filename)
    {
        $this->open($filename);
    }

    public function setForceAnsiColorSupport($force = true)
    {
        $this->force_ansi_color_support = $force;
    }

    public function setForceNoAnsiColorSupport($force = true)
    {
        $this->force_no_ansi_color_support = $force;
    }

    private function canUseColor()
    {
        if ($this->force_ansi_color_support) {
            return true;
        } elseif ($this->force_no_ansi_color_support) {
            return false;
        } else {
            return function_exists('posix_isatty') && @posix_isatty($this->ressource);
        }
    }

    private function open($filename)
    {
        $this->ressource = fopen($filename, 'a');

        if (!$this->ressource) {
            throw new \Exception('Could not open file : ' . $filename);
        }
    }

    public function write(string $line, ?AnsiTextColor $text_color = null, ?AnsiBackgroundColor $background_color = null, bool $add_eol = true): void
    {
        if (!$this->ressource) {
            throw new \Exception('No file opened, cannot write');
        }

        $line = $this->addColor($line, $text_color, $background_color);

        fwrite($this->ressource, $line . ($add_eol ? "\n" : ''));
    }

    private function addColor(string $line, ?AnsiTextColor $text_color, ?AnsiBackgroundColor $background_color): string
    {
        $colored_line = '';
        $is_colored = false;

        if ($this->canUseColor()) {
            if ($text_color !== null) {
                $is_colored = true;
                $colored_line .= self::ESCAPE_SEQUENCE . $text_color->value . self::END_SEQUENCE;
            }

            if ($background_color !== null) {
                $is_colored = true;
                $colored_line .= self::ESCAPE_SEQUENCE . $background_color->value . self::END_SEQUENCE;
            }
        }

        $colored_line .= $line;

        if ($is_colored) {
            $colored_line .= self::NO_COLOR;
        }

        return $colored_line;
    }

    public function __destruct()
    {
        if ($this->ressource) {
            @fclose($this->ressource);
        }
    }
}
