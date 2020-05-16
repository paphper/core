<?php

namespace Paphper\Parsers;

use Paphper\Contracts\MetaInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

class PaperTagContentParser extends AbstractPaperTagParser implements MetaInterface
{
    public function getContent(): PromiseInterface
    {
        $deferred = new Deferred();
        $deferred->resolve($this->filename);

        return  $deferred->promise();
    }
}
