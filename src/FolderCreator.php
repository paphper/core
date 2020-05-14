<?php

namespace Paphper;

use React\Filesystem\FilesystemInterface;
use React\Filesystem\Node\File;
use React\Promise\PromiseInterface;

class FolderCreator
{
    private $filesystem;
    private $config;

    public function __construct(FilesystemInterface $filesystem, Config $config)
    {
        $this->filesystem = $filesystem;
        $this->config = $config;
    }

    public function getFoldersToCreate(): PromiseInterface
    {
        return $this->filesystem
            ->dir($this->config->getPageBaseFolder())
            ->lsRecursive()
            ->then(function ($lists) {
                $allFolders = [];
                foreach ($lists as $item) {
                    if ($item instanceof File) {
                        $allFolders[] = (new BuildFileResolver($this->config, (string) $item))->getFolder();
                    }
                }
                $folderParser = new FolderParser($allFolders);

                return $folderParser->parse();
            });
    }
}
