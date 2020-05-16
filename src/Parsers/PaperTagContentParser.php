<?php


namespace Paphper\Parsers;


use Paphper\Contracts\MetaInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

class PaperTagContentParser extends AbstractPaperTagParser implements MetaInterface
{
    public function getContent(): PromiseInterface
    {
        $deferred = new Deferred();
        $deferred->resolve($this->filename);
        return  $deferred->promise();
    }
}
