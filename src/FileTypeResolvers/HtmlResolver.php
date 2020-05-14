<?php

namespace Paphper\FileTypeResolvers;

use Paphper\Config;
use Paphper\Contents\Html;
use Paphper\Interfaces\ContentInterface;
use Paphper\Interfaces\ContentResolverInterface;
use React\Filesystem\FilesystemInterface;

class HtmlResolver implements ContentResolverInterface
{
    private $config;
    private $filesystem;

    public function __construct(Config $config, FilesystemInterface $filesystem)
    {
        $this->config = $config;
        $this->filesystem = $filesystem;
    }

    public function resolveFileType(string $filename): ContentInterface
    {
        return new Html($this->config, $this->filesystem, $filename);
    }
}
