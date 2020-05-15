<?php

namespace Paphper\Contracts;

interface ContentResolverInterface
{
    public function resolveFileType(string $filename): ContentInterface;
}
