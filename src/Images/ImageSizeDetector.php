<?php

namespace Paphper\Images;

use Paphper\Utils\Str;

class ImageSizeDetector
{
    private $images = [];
    private $sizes = [];

    public function __construct(array $images)
    {
        $this->images = $images;

        foreach ($this->images as $image) {
            $this->checkResize($image);
        }
    }

    public function getSizes(): array
    {
        return $this->sizes;
    }

    public function getOriginals(): array
    {
        return array_keys($this->sizes);
    }

    private function checkResize(string $filename)
    {
        preg_match('[_(\d+[x{,1}]\d+)]', $filename, $matches);
        if (2 === count($matches)) {
            $originalName = (new Str($filename))->replaceAllWith($matches[0], '');
            if (!isset($this->sizes[$originalName])) {
                $this->sizes[$originalName] = [];
            }
            $this->sizes[$originalName][] = end($matches);

            return;
        }

        $this->sizes[$filename] = [];
    }
}
