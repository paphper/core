<?php


namespace Paphper\Parsers;


use Paphper\Config;
use React\Filesystem\FilesystemInterface;
use React\Promise\PromiseInterface;

class AbstractPaperTagParser
{
    protected $finalCollection = [];
    protected $body;
    protected $content;
    protected $endTag = '</paper>';
    protected $startTag = '<paper>';
    protected $filesystem;
    protected $filename;
    protected $config;

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

    protected function getContent(): PromiseInterface
    {
        return $this->filesystem->file($this->filename)->getContents();
    }

    protected function getMeta($content)
    {
        $endOfMeta = strpos($content, $this->endTag);

        return substr($content, strlen($this->startTag), ($endOfMeta - strlen($this->endTag)));
    }

    protected function parseBody()
    {
        $endOfMeta = strpos($this->content, $this->endTag);

        return substr($this->content, $endOfMeta + strlen($this->endTag));
    }
}
