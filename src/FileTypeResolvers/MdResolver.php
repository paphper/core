<?php

namespace Paphper\FileTypeResolvers;

use Paphper\Config;
use Paphper\Contents\Md;
use Paphper\Contents\MetaParser;
use Paphper\Contracts\ContentInterface;
use Paphper\Contracts\ContentResolverInterface;
use React\Filesystem\FilesystemInterface;

class MdResolver implements ContentResolverInterface
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
        $meta = new MetaParser($this->config, $this->filesystem, $filename);

        return new Md($meta);
    }
}
