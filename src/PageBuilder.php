<?php

namespace Paphper;

use Paphper\Interfaces\MetaInterface;

class PageBuilder
{
    private $parser;
    private $layoutContent;

    public function __construct(MetaInterface $parser, string $layoutContent)
    {
        $this->parser = $parser;
        $this->layoutContent = $layoutContent;
    }

    public function toHtml(): string
    {
        $content = $this->parser->getBody();
        $content = str_replace('{content}', $content, $this->layoutContent);
        foreach ($this->parser->getExtraMetas() as $key => $meta) {
            $key = '{'.$key.'}';
            $content = str_replace($key, $meta, $content);
        }

        return  $content;
    }
}
