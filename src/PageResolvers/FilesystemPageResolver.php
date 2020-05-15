<?php

namespace Paphper\PageResolvers;

use Paphper\BuildFileResolver;
use Paphper\Config;
use Paphper\Contracts\PageResolverInterface;
use React\Filesystem\FilesystemInterface;
use React\Filesystem\Node\File;
use React\Promise\PromiseInterface;

class FilesystemPageResolver implements PageResolverInterface
{
    private $filesystem;
    private $config;
    private $pages;

    public function __construct(Config $config, FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->pages = $this->resolvePages();
    }

    public function getPages(): PromiseInterface
    {
        return $this->pages;
    }

    public function getBuildFilename(string $page): string
    {
        return (new BuildFileResolver($this->config, $page))->getName();
    }

    public function getBuildFolder(string $page): string
    {
        return (new BuildFileResolver($this->config, $page))->getFolder();
    }

    private function resolvePages(): PromiseInterface
    {
        return $this->filesystem->dir($this->config->getPageBaseFolder())
            ->lsRecursive()
            ->then(function ($nodes) {
                $pages = [];
                foreach ($nodes as $node) {
                    if ($node instanceof File) {
                        $pages[] = (string) $node;
                    }
                }

                return $pages;
            });
    }
}
