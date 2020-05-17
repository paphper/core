<?php

namespace Paphper\Responses;

use Intervention\Image\ImageManager;
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

    public static function create(ServerRequestInterface $request, Config $config, FilesystemInterface $filesystem, ImageManager $manager)
    {
        if (self::isImage($request->getUri()->getPath()) || self::isCss($request->getUri()->getPath())) {
            return new Asset($request, $config, $filesystem, $manager);
        }

        return new Html($request, $config, $filesystem);
    }

    public static function isCss(string $path)
    {
        return (new Str($path))->endsWith('css');
    }

    private static function isImage(string $path)
    {
        return (new Str(strtolower($path)))->endsWithAny(self::$imageFileExtensions);
    }
}
