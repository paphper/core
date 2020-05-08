<?php


namespace Paphper\Contents;


use Paphper\FileReader;
use Paphper\Utils\Str;
use React\Filesystem\FilesystemInterface;

class Factory
{

    public static function create(FilesystemInterface $filesystem, string $filename)
    {
        $string = new Str($filename);

        if($string->endsWith('.html')) {
            return new Html(new FileReader($filesystem, $filename));
        }

        if($string->endsWith('.md')) {
            return new Md(new FileReader($filesystem, $filename));
        }
    }
}
