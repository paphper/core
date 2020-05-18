<?php

namespace Paphper\Contents;

use Paphper\Config;
use Paphper\Contracts\ContentInterface;
use Paphper\Contracts\MetaInterface;
use Paphper\Parsers\PaperTagContentParser;
use Paphper\Utils\Str;
use React\Filesystem\FilesystemInterface;
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

        $meta = new PaperTagContentParser($this->config, $this->filesystem, $content);

        return $meta->process()
            ->then(function (MetaInterface $meta) use ($content) {
                $content = trim($meta->getBody());
                foreach ($meta->getExtraMetas() as $key => $meta) {
                    $key = '{'.$key.'}';
                    $content = str_replace($key, $meta, $content);
                }

                return  $content;
            });
    }
}
