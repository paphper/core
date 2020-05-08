<?php

namespace Paphper;

use React\Filesystem\FilesystemInterface;
use React\Promise\PromiseInterface;

class FileReader
{
    private $content;
    private $filename;

    public function __construct(FilesystemInterface $filesystem, string $filename)
    {
        $this->filename = $filename;
        $this->content = $filesystem->getContents($filename);
    }

    public function getContent() : PromiseInterface
    {
        return $this->content;
    }
}
