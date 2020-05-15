<?php

namespace Paphper\Utils;

use React\Filesystem\FilesystemInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class FileCreator
{
    private $filesystem;
    private $filename;
    private $directory;
    private $content;
    private $folderName;

    public function __construct(FilesystemInterface $filesystem, string $filename, string $content)
    {
        $this->filesystem = $filesystem;
        $this->filename = $filename;
        $this->folderName = (new Str($filename))->getBeforeLast('/');
        $this->content = $content;
    }

    public function writeFile(): PromiseInterface
    {
        $deferred = new Deferred();

        $this->createDirectory()
            ->then(function () use ($deferred) {
                $file = $this->filesystem->file($this->filename);
                $file->exists()
                    ->then(function () use ($file) {
                        $file->remove();
                    }, function () {
                    })->then(function () use ($file, $deferred) {
                        $file->putContents($this->content)
                            ->then(function () use ($deferred) {
                                $deferred->resolve($this->filename);
                            });
                    });
            });

        return $deferred->promise();
    }

    private function createDirectory(): PromiseInterface
    {
        $deferred = new Deferred();

        $this->directory = $this->filesystem->dir($this->folderName);
        $this->directory->stat()
            ->then(function () use ($deferred) {
                $deferred->resolve($this->directory);
            }, function () use ($deferred) {
                $this->directory->createRecursive('rwxrwx---')
                    ->then(function () use ($deferred) {
                        $deferred->resolve($this->directory);
                    });
            });

        return $deferred->promise();
    }
}
