<?php

namespace Paphper\Contents;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use Paphper\Contracts\ContentInterface;
use Paphper\Contracts\MetaInterface;
use Paphper\PageBuilder;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class Md implements ContentInterface
{
    protected $file;
    protected $meta;

    public function __construct(MetaInterface $meta)
    {
        $this->meta = $meta;
    }

    public function getPageContent(): PromiseInterface
    {
        $deferred = new Deferred();

        $this->meta->process()
            ->then(function (MetaInterface $meta) use (&$deferred) {
                $meta->getLayoutContent()
                    ->then(function ($layoutContent) use (&$deferred, $meta) {
                        $converter = new GithubFlavoredMarkdownConverter([
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                        ]);

                        $pageBuilder = new PageBuilder($meta, $layoutContent, $converter->convertToHtml($meta->getBody()));
                        $deferred->resolve($pageBuilder->toHtml());
                    });
            });

        return $deferred->promise();
    }
}
