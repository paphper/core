<?php

namespace Paphper\Contents;

use Paphper\FileReader;
use React\Promise\PromiseInterface;

class AbstractContentFile
{
    protected $fileReader;
    private $endTag = '</paper>';
    private $startTag= '<paper>';

    public function __construct(FileReader $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    public function getContent(): PromiseInterface
    {
        return $this->fileReader->getContent();
    }

    public function getMetaData(): PromiseInterface
    {
        return $this->getContent()
            ->then(function ($content) {
            return new MetaParser($this->getMeta($content), $this->getBody($content));
        });
    }

    public function getFileReader(): FileReader
    {
        return $this->fileReader;
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
