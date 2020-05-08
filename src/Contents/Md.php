<?php

namespace Paphper\Contents;

use Paphper\Contents\Interfaces\HasMetaData;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use React\Promise\PromiseInterface;

class Md extends AbstractContentFile implements HasMetaData
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
