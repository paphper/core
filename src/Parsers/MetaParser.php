<?php

namespace Paphper\Parsers;

use Paphper\Config;
use Paphper\Contracts\MetaInterface;
use React\Filesystem\FilesystemInterface;
use React\Promise\PromiseInterface;

class MetaParser implements MetaInterface
{
    private $finalCollection = [];
    private $body;
    private $content;
    private $endTag = '</paper>';
    private $startTag = '<paper>';
    private $filesystem;
    private $filename;
    private $config;

    public function __construct(Config $config, FilesystemInterface $filesystem, string $filename)
    {
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->filename = $filename;
    }

    public function getLayout(): string
    {
        return $this->get('layout');
    }

    public function get(string $key): ?string
    {
        return $this->finalCollection[$key] ?? null;
    }

    public function getExtraMetas(): array
    {
        $extraMetas = $this->finalCollection;
        unset($extraMetas['layout']);

        return $this->finalCollection;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getLayoutContent(): PromiseInterface
    {
        $layoutFile = $this->config->getLayoutBaseFolder().'/'.$this->getLayout();

        return $this->filesystem->file($layoutFile)->getContents();
    }

    public function process(): PromiseInterface
    {
        return $this->getContent()->then(function ($content) {
            $this->content = $content;
            $this->body = $this->parseBody();
            $attributes = explode(PHP_EOL, $this->getMeta($content));
            foreach (array_filter($attributes) as $attr) {
                [$key, $value] = explode(':', $attr);
                $this->finalCollection[trim($key)] = trim($value);
            }

            return $this;
        });
    }

    private function getContent(): PromiseInterface
    {
        return $this->filesystem->file($this->filename)->getContents();
    }

    private function getMeta($content)
    {
        $endOfMeta = strpos($content, $this->endTag);

        return substr($content, strlen($this->startTag), ($endOfMeta - strlen($this->endTag)));
    }

    private function parseBody()
    {
        $endOfMeta = strpos($this->content, $this->endTag);

        return substr($this->content, $endOfMeta + strlen($this->endTag));
    }
}
