<?php

namespace Paphper\Contents;


use Paphper\Contents\Interfaces\HasMetaData;
use Paphper\FileReader;
use React\Promise\PromiseInterface;

class Html extends AbstractContentFile implements HasMetaData
{
    protected $fileReader;

}
