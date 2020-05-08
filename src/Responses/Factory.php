<?php

namespace Paphper\Responses;

use Paphper\Config;
use Paphper\Utils\Str;
use Psr\Http\Message\ServerRequestInterface;
use React\Filesystem\FilesystemInterface;

class Factory
{

    private static $imageFileExtensions = [
        'jpg',
        'jpeg',
        'png',
        'ico',
        'svg',
    ];


    public static function create(ServerRequestInterface $request, Config $config, FilesystemInterface $filesystem)
    {
        if (self::isImage($request->getUri()) || self::isCss($request->getUri())) {
            return new Asset($request, $config, $filesystem );
        }

        return new Html($request, $config, $filesystem);
    }

    private static function isImage(string $path)
    {
        return (new Str(strtolower($path)))->endsWithAny(self::$imageFileExtensions);
    }

    public static function isCss(string $path)
    {
        return (new Str($path))->endsWith('css');
    }
}
