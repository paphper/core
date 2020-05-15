<?php

namespace Paphper\Interfaces;

interface ContentResolverInterface
{
    public function resolveFileType(string $filename): ContentInterface;
}
