<?php


namespace Paphper\Contents\Interfaces;


use React\Filesystem\Node\File;

interface Buildable
{

    public function resolve(File $file);
}
