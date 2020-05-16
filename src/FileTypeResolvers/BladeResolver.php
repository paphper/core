<?php


namespace Paphper\FileTypeResolvers;

use Paphper\Config;
use Paphper\Contents\Blade;
use Paphper\Contracts\ContentInterface;
use Paphper\Contracts\ContentResolverInterface;
use Paphper\Utils\Str;
use React\Filesystem\FilesystemInterface;

class BladeResolver implements ContentResolverInterface
{
    private $config;
    private $blade;
    private $filesystem;

    public function __construct(Config $config, FilesystemInterface $filesystem)
    {
        $this->config = $config;
        $this->filesystem = $filesystem;
        $baseFolder = $this->config->getPageBaseFolder();

        $this->blade = new \Jenssegers\Blade\Blade( (new Str($baseFolder))->getBeforeLast('/'), $this->config->getCacheDir());
    }

    public function resolveFileType(string $filename): ContentInterface
    {
       return new Blade($this->config, $this->blade, $this->filesystem, $filename);
    }

}
