<?php

namespace Paphper;

use Paphper\Contents\MetaParser;
use Paphper\Interfaces\ContentInterface;
use React\Promise\Deferred;

class HtmlGenerator
{
    private $content;
    private $config;

    public function __construct(Config $config, ContentInterface $content)
    {
        $this->content = $content;
        $this->config = $config;
    }

    public function getHtml()
    {
        $deferred = new Deferred();

        $this->content->getMetaData()
            ->then(function (MetaParser $parser) use (&$deferred) {
                $this->content->getLayoutContent()
                    ->then(function ($layoutContent) use ($parser, &$deferred) {
                        $pageBuilder = new PageBuilder($parser, $layoutContent);
                        $deferred->resolve($pageBuilder->toHtml());
                    });
            });

        return $deferred->promise();
    }
}
