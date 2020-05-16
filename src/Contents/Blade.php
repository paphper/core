<?php

namespace Paphper\Contents;

use Paphper\Config;
use Paphper\Contracts\ContentInterface;
use Paphper\Contracts\MetaInterface;
use Paphper\PageBuilder;
use Paphper\Parsers\PaperTagContentParser;
use Paphper\Parsers\PaperTagParser;
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


        $content = $this->blade->render('pages' . $filename);

        $defered = new Deferred();
        $defered->resolve($content);
        return $defered->promise();


//        $meta = new PaperTagContentParser($this->config, $this->filesystem, $content);

//        return $meta->process()
//            ->then(function (MetaInterface $meta) {
//                return $meta->getLayoutContent()->then(function ($layoutContent) use ($meta) {
//                    $builder = new PageBuilder($meta, $layoutContent, $meta->getBody());
//                    return $builder->toHtml();
//                });
//            });
    }
}
