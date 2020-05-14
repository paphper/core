<?php

namespace Paphper\Contents;

use Paphper\Config;
use Paphper\Interfaces\MetaInterface;
use Paphper\PageBuilder;
use React\Filesystem\FilesystemInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class AbstractContentFile
{
    protected $file;
    protected $filesystem;
    protected $filename;
    protected $config;
    private $endTag = '</paper>';
    private $startTag = '<paper>';

    public function __construct(Config $config, FilesystemInterface $filesystem, string $filename)
    {
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->filename = $filename;
    }

    public function getContent(): PromiseInterface
    {
        return $this->filesystem->file($this->filename)->getContents();
    }

    public function getMetaData(): PromiseInterface
    {
        return $this->getContent()
            ->then(function ($content) {
                return new MetaParser($this->getMeta($content), $this->getBody($content));
            });
    }

    public function getLayoutContent(): PromiseInterface
    {
        return $this->getMetaData()
            ->then(function (MetaInterface $parser) {
                return $this->filesystem->file($this->config->getLayoutBaseFolder().'/'.$parser->getLayout())
                    ->getContents();
            });
    }

    public function getPageContent(): PromiseInterface
    {
        $deferred = new Deferred();
        $this->getLayoutContent()
            ->then(function ($layoutContent) use (&$deferred) {
                $this->getMetaData()
                    ->then(function (MetaInterface $meta) use ($layoutContent, &$deferred) {
                        $pageBuilder = new PageBuilder($meta, $layoutContent);
                        $deferred->resolve($pageBuilder->toHtml());
                    });
            });

        return $deferred->promise();
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    protected function getBody($content)
    {
        $endOfMeta = strpos($content, $this->endTag);

        return substr($content, $endOfMeta + strlen($this->endTag));
    }

    protected function getMeta($content)
    {
        $endOfMeta = strpos($content, $this->endTag);

        return substr($content, strlen($this->startTag), ($endOfMeta - strlen($this->endTag)));
    }
}
