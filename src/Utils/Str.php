<?php

namespace Paphper\Utils;

class Str
{
    private $string;

    public function __construct(string $string)
    {
        $this->string = $string;
    }

    public function endsWith(string $string)
    {
        if (strlen($this) < strlen($string)) {
            return false;
        }

        $position = (int)('-' . strlen($string));

        return (strlen($this) - strlen($string)) === strpos($this, $string, $position);
    }

    public function startsWith(string $string)
    {
        if (strlen($this) < strlen($string)) {
            return false;
        }

        return strpos($this, $string) === 0;
    }

    public function replaceAllWith(string $search, string $replace)
    {
        return str_replace($search, $replace, $this);
    }

    public function replaceLastWith(string $search, string $replace)
    {
        $length = strlen($search);
        $position = (int)('-' . $length);

        return substr_replace($this, $replace, $position, $length);
    }

    public function removeLast(string $string): string
    {
        return $this->replaceLastWith($string, '');
    }

    public function getAfterLast(string $string)
    {
        $parts = explode($string, $this);

        return count($parts) === 1 ? '' : end($parts);
    }

    public function getBeforeLast(string $lastString)
    {
        $strArr = explode($lastString, $this);
        unset($strArr[count($strArr) - 1]);

        return implode($lastString, $strArr);
    }

    /**
     * @param string [] $strings
     */
    public function endsWithAny(array $strings)
    {
        foreach ($strings as $string) {
            if ($this->endsWith($string)) {
                return true;
            }
        }
        return false;
    }

    public function __toString()
    {
        return $this->string;
    }

}
