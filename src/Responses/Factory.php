<?php

namespace Paphper\Responses;

use Intervention\Image\ImageManager;
use Paphper\Config;
use Paphper\Utils\Str;
use Psr\Http\Message\ServerRequestInterface;
use React\Filesystem\FilesystemInterface;

class Factory
{
    private static $assetFileExtensions = [
        'jpg',
        'jpeg',
        'png',
        'ico',
        'svg',
        'css',
        'js',
        'txt',
        'xml',
    ];

    public static function create(ServerRequestInterface $request, Config $config, FilesystemInterface $filesystem, ImageManager $manager)
    {
        if (self::isAsset($request->getUri()->getPath())) {
            return new Asset($request, $config, $filesystem, $manager);
        }

        return new Html($request, $config, $filesystem);
    }

    public static function isAsset(string $path)
    {
        return (new Str(strtolower($path)))->endsWithAny(self::$assetFileExtensions);
    }
}
