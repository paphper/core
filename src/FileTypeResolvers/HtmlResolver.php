<?php

namespace Paphper\FileTypeResolvers;

use Paphper\Config;
use Paphper\Contents\Html;
use Paphper\Contracts\ContentInterface;
use Paphper\Contracts\ContentResolverInterface;
use Paphper\Parsers\PaperTagParser;
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
        $meta = new PaperTagParser($this->config, $this->filesystem, $filename);

        return new Html($meta);
    }
}
