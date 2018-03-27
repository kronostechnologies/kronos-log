<?php

namespace Kronos\Log\Traits;

trait Interpolate
{
    public function interpolate($message, array $context = [])
    {
        $translation = [];
        $placeholders = $this->getPlaceholders($message);
        foreach ($placeholders as $placeholder => $key) {
            $value = $this->getContextForPlaceholderKey($context, $key);
            if ($this->canBeInterpolated($value)) {
                $translation[$placeholder] = (string)$value;
            } else {
                $translation[$placeholder] = '~UNDEFINED~';
            }
        }
        return strtr($message, $translation);
    }

    private function getPlaceholders($message)
    {
        $keys = [];
        preg_match_all('/(\{([a-zA-Z0-9._]+)\})/', $message, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $keys[$match[1]] = $match[2];
        }
        return $keys;
    }

    private function getContextForPlaceholderKey($context, $placeholder)
    {
        return isset($context[$placeholder]) ? $context[$placeholder] : null;
    }

    /**
     * @param $value
     * @return bool
     */
    private function canBeInterpolated($value)
    {
        return $value && !is_array($value) && (!is_object($value) || method_exists($value, '__toString'));
    }
}