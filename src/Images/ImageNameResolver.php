<?php

namespace Paphper\Images;

use Paphper\Utils\Str;

class ImageNameResolver
{
    private $filename;
    private $size;

    public function __construct(string $filename, string $size)
    {
        $this->filename = $filename;
        $this->size = $size;
    }

    public function getFilename(): string
    {
        $nameWithoutExt = (new Str($this->filename))->getBeforeLast('.');
        $extension = (new Str($this->filename))->getAfterLast('.');

        return $nameWithoutExt.'_'.$this->size.'.'.$extension;
    }
}
