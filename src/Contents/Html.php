<?php

namespace Paphper\Contents;

use Paphper\Interfaces\ContentInterface;
use Paphper\Interfaces\MetaInterface;
use Paphper\PageBuilder;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class Html implements ContentInterface
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
                        $pageBuilder = new PageBuilder($meta, $layoutContent, $meta->getBody());
                        $deferred->resolve($pageBuilder->toHtml());
                    });
            });

        return $deferred->promise();
    }
}
