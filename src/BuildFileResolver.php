<?php

namespace Paphper;

use Paphper\Utils\Str;

class BuildFileResolver
{
    private $config;
    private $filename;
    private $name;

    public function __construct(Config $config, string $filename)
    {
        $this->config = $config;
        $this->filename = $filename;
        $folderParser = new FolderParser([$filename]);
        [$rawName] = $folderParser->parse();
        $this->name = str_replace($this->config->getPageBaseFolder(), $this->config->getBuildBaseFolder(), $rawName).'/index.html';
    }

    public function getName(): string
    {
        return  $this->name;
    }

    public function getFolder()
    {
        return (new Str($this->name))->getBeforeLast('/');
    }
}
