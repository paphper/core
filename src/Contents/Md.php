<?php

namespace Paphper\Contents;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use Paphper\Interfaces\ContentInterface;
use React\Promise\PromiseInterface;

class Md extends AbstractContentFile implements ContentInterface
{
    public function getMetaData(): PromiseInterface
    {
        return $this->getContent()
            ->then(function ($content) {
                $body = $this->getBody($content);
                $converter = new GithubFlavoredMarkdownConverter([
                    'html_input' => 'strip',
                    'allow_unsafe_links' => false,
                ]);

                return new MetaParser($this->getMeta($content), $converter->convertToHtml($body));
            });
    }
}
