<?php

namespace Kronos\Log;

use function PHPSTORM_META\type;

class ContextStringifier
{

    private $excluded_keys = [];

    /**
     * Transform context array to a printable string
     * @param array $context
     * @return string
     */
    public function stringify(array $context)
    {
        $string = '';

        $key_count = 0;
        foreach ($context as $key => $value) {
            if (!in_array($key, $this->excluded_keys)) {
                $string .= ($key_count++ > 0 ? PHP_EOL : '') . $key . ': ' . $this->stringifyValue($value);
            }
        }

        return $string;
    }

    /**
     * Transform context values and objects to strings recursively
     * @param array $context
     * @return array
     */
    public function stringifyArray(array $context)
    {
        $stringifiedArray = [];

        foreach ($context as $index => $value) {
            if (!in_array($index, $this->excluded_keys)) {
                if (is_array($value)) {
                    if (!empty($value)) {
                        $nestedArray = $this->stringifyArray($value);
                        if (!empty($nestedArray)) {
                            $stringifiedArray[$index] = $nestedArray;
                        }
                    }
                } elseif (is_object($value)) {
                    $stringifiedArray[$index] = $this->stringifyObject($value);
                } else {
                    $stringifiedArray[$index] = (string)$value;
                }
            }
        }

        return $stringifiedArray;
    }

    private function stringifyValue($value)
    {
        if (is_array($value)) {
            return print_r($value, true);
        }
        if (is_object($value)) {
            return $this->stringifyObject($value);
        } else {
            return $value;
        }
    }

    private function stringifyObject($value)
    {
        if (method_exists($value, '__toString')) {
            return (string)$value;
        } else {
            return print_r($value, true);
        }
    }

    public function excludeKey($key)
    {
        $this->excluded_keys[] = $key;
    }
}