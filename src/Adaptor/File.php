<?php

namespace Kronos\Log\Adaptor;

class File
{
    private $filename;
    private $ressource;

    public function __construct($filename)
    {
        if ($this->fileExistsAndWriteable($filename)) {
            $this->filename = $filename;
        } else {
            trigger_error('File is not writeable : ' . $filename, E_USER_WARNING);
        }
    }

    private function open($filename)
    {
        $this->ressource = @fopen($filename, 'a');

        if (!$this->ressource) {
            throw new \Exception('Could not open file : ' . $filename);
        }
    }

    public function write($line, $add_eol = true)
    {
        try {
            if (!$this->ressource) {
                $this->open($this->filename);
            }

            fwrite($this->ressource, $line . ($add_eol ? "\n" : ''));
        } catch (\Exception $exception) {
            trigger_error(
                'An error occured while writing to file ' . $this->filename . ': ' . $exception->getMessage(),
                E_USER_WARNING
            );
        }
    }

    public function __destruct()
    {
        if ($this->ressource) {
            @fclose($this->ressource);
        }
    }

    /**
     * @param $filename
     * @return bool
     */
    private function fileExistsAndWriteable($filename)
    {
        return (file_exists($filename) && is_writeable($filename)) || (is_dir(dirname($filename)) && is_writeable(dirname($filename)));
    }
}
