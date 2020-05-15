<?php

namespace Paphper;

use Paphper\Contracts\MetaInterface;

class PageBuilder
{
    private $parser;
    private $layoutContent;
    private $body;

    public function __construct(MetaInterface $parser, string $layoutContent, string $body)
    {
        $this->parser = $parser;
        $this->layoutContent = $layoutContent;
        $this->body = $body;
    }

    public function toHtml(): string
    {
        $content = str_replace('{content}', $this->body, $this->layoutContent);
        foreach ($this->parser->getExtraMetas() as $key => $meta) {
            $key = '{'.$key.'}';
            $content = str_replace($key, $meta, $content);
        }

        return  $content;
    }
}
