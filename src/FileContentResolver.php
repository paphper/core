<?php

namespace Paphper;

use Paphper\Exceptions\ContentResolverException;
use Paphper\Contracts\ContentInterface;
use Paphper\Contracts\ContentResolverInterface;
use Paphper\Utils\Str;

class FileContentResolver
{
    private $resolvers = [];
    private $extensions = [];

    public function add(string $fileType, ContentResolverInterface $contentResolver)
    {
        $this->extensions[] = $fileType;
        $this->resolvers[$fileType] = $contentResolver;
    }

    public function resolve(string $filename): ContentInterface
    {
        foreach ($this->extensions as $extension) {
            if ((new Str($filename))->endsWith($extension)) {
                return $this->resolvers[$extension]->resolveFileType($filename);
            }
        }

        throw new ContentResolverException((new Str($filename))->getAfterLast('/').'file does not have a content resolver.');
    }
}
