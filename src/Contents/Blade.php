<?php

namespace Paphper\Contents;

use Paphper\Config;
use Paphper\Contracts\ContentInterface;
use Paphper\Utils\Str;
use React\Filesystem\FilesystemInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class Blade implements ContentInterface
{
    private $blade;
    private $filename;
    private $config;
    private $filesystem;

    public function __construct(Config $config, \Jenssegers\Blade\Blade $blade, FilesystemInterface $filesystem, string $filename)
    {
        $this->config = $config;
        $this->filename = $filename;
        $this->blade = $blade;
        $this->filesystem = $filesystem;
    }

    public function getPageContent(): PromiseInterface
    {
        $filename = (new Str($this->filename))->getBeforeLast('.blade.php');
        $filename = (new Str($filename))->replaceAllWith($this->config->getPageBaseFolder(), '');

        $content = $this->blade->render('pages'.$filename);
        $defered = new Deferred();
        $defered->resolve($content);

        return $defered->promise();
    }
}
